const express = require("express");
const path = require("path");
const session = require("express-session");
const bcrypt = require("bcrypt");
const connectPgSimple = require("connect-pg-simple");
const { pool, initializeDatabase } = require("./db");

const app = express();
const port = process.env.PORT || 3000;
const PgStore = connectPgSimple(session);
let databaseReady = false;
const sessionStore =
  process.env.SESSION_STORE === "postgres"
    ? new PgStore({
        pool,
        tableName: "user_sessions",
        createTableIfMissing: true,
      })
    : undefined;

app.set("view engine", "ejs");
app.set("views", path.join(__dirname, "views"));

app.use(express.urlencoded({ extended: false }));
app.use(express.static(path.join(__dirname, "public")));
app.use(
  session({
    store: sessionStore,
    secret: process.env.SESSION_SECRET || "zomium-change-this-session-secret",
    resave: false,
    saveUninitialized: false,
    cookie: {
      httpOnly: true,
      sameSite: "lax",
      secure: process.env.NODE_ENV === "production",
      maxAge: 1000 * 60 * 60 * 24 * 7,
    },
  }),
);

app.use((req, res, next) => {
  res.locals.currentUser = req.session.user || null;
  res.locals.error = null;
  res.locals.formData = {};
  next();
});

function normalizeUsername(value) {
  return value.trim().toLowerCase();
}

function normalizeEmail(value) {
  return value.trim().toLowerCase();
}

function renderAuthPage(res, viewName, options = {}) {
  res.status(options.statusCode || 200).render(viewName, {
    error: options.error || null,
    formData: options.formData || {},
  });
}

function createAuthenticatedSession(req, user) {
  return new Promise((resolve, reject) => {
    req.session.regenerate((error) => {
      if (error) {
        return reject(error);
      }

      req.session.user = user;
      return resolve();
    });
  });
}

async function ensureDatabaseReady() {
  if (databaseReady) {
    return true;
  }

  try {
    await initializeDatabase();
    databaseReady = true;
    return true;
  } catch (error) {
    console.error("Database initialization failed:", error);
    return false;
  }
}

function redirectIfAuthenticated(req, res, next) {
  if (req.session.user) {
    return res.redirect("/my/home");
  }

  next();
}

function requireAuth(req, res, next) {
  if (!req.session.user) {
    return res.redirect("/login");
  }

  next();
}

function validateRegistrationInput({ username, email, password }) {
  if (!username || !email || !password) {
    return "All fields are required.";
  }

  if (!/^[a-zA-Z0-9_]{3,24}$/.test(username)) {
    return "Username must be 3-24 characters and only contain letters, numbers, or underscores.";
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    return "Please enter a valid email address.";
  }

  if (password.length < 8) {
    return "Password must be at least 8 characters.";
  }

  return null;
}

app.get("/", (req, res) => {
  if (req.session.user) {
    return res.redirect("/my/home");
  }

  res.render("index");
});

app.get("/register", redirectIfAuthenticated, (req, res) => {
  renderAuthPage(res, "register");
});

app.post("/register", redirectIfAuthenticated, async (req, res) => {
  const username = (req.body.username || "").trim();
  const email = (req.body.email || "").trim();
  const password = req.body.password || "";
  const validationError = validateRegistrationInput({ username, email, password });

  if (!(await ensureDatabaseReady())) {
    return renderAuthPage(res, "register", {
      statusCode: 503,
      error: "Database connection is not ready yet. Check PostgreSQL settings and try again.",
      formData: { username, email },
    });
  }

  if (validationError) {
    return renderAuthPage(res, "register", {
      statusCode: 400,
      error: validationError,
      formData: { username, email },
    });
  }

  const usernameNormalized = normalizeUsername(username);
  const emailNormalized = normalizeEmail(email);

  try {
    const passwordHash = await bcrypt.hash(password, 12);
    const result = await pool.query(
      `
        INSERT INTO users (username, username_normalized, email, email_normalized, password_hash)
        VALUES ($1, $2, $3, $4, $5)
        RETURNING id, username
      `,
      [username, usernameNormalized, email, emailNormalized, passwordHash],
    );

    await createAuthenticatedSession(req, {
      id: result.rows[0].id,
      username: result.rows[0].username,
    });

    return res.redirect("/my/home");
  } catch (error) {
    if (error.code === "23505") {
      return renderAuthPage(res, "register", {
        statusCode: 409,
        error: "That username or email is already in use.",
        formData: { username, email },
      });
    }

    console.error("Register failed:", error);
    return renderAuthPage(res, "register", {
      statusCode: 500,
      error: "Something went wrong while creating your account.",
      formData: { username, email },
    });
  }
});

app.get("/my/home", requireAuth, (req, res) => {
  res.render("my/home");
});

app.get("/login", redirectIfAuthenticated, (req, res) => {
  renderAuthPage(res, "login");
});

app.get("/games", requireAuth, (req, res) => {
  res.render("games");
});

app.get("/catalog", requireAuth, (req, res) => {
  res.render("catalog");
});

app.post("/login", redirectIfAuthenticated, async (req, res) => {
  const username = (req.body.username || "").trim();
  const password = req.body.password || "";

  if (!username || !password) {
    return renderAuthPage(res, "login", {
      statusCode: 400,
      error: "Username and password are required.",
      formData: { username },
    });
  }

  if (!(await ensureDatabaseReady())) {
    return renderAuthPage(res, "login", {
      statusCode: 503,
      error: "Database connection is not ready yet. Check PostgreSQL settings and try again.",
      formData: { username },
    });
  }

  try {
    const result = await pool.query(
      `
        SELECT id, username, password_hash
        FROM users
        WHERE username_normalized = $1
        LIMIT 1
      `,
      [normalizeUsername(username)],
    );

    if (result.rows.length === 0) {
      return renderAuthPage(res, "login", {
        statusCode: 401,
        error: "Invalid username or password.",
        formData: { username },
      });
    }

    const user = result.rows[0];
    const passwordMatches = await bcrypt.compare(password, user.password_hash);

    if (!passwordMatches) {
      return renderAuthPage(res, "login", {
        statusCode: 401,
        error: "Invalid username or password.",
        formData: { username },
      });
    }

    await createAuthenticatedSession(req, {
      id: user.id,
      username: user.username,
    });

    return res.redirect("/my/home");
  } catch (error) {
    console.error("Login failed:", error);
    return renderAuthPage(res, "login", {
      statusCode: 500,
      error: "Something went wrong while logging in.",
      formData: { username },
    });
  }
});

app.post("/logout", requireAuth, (req, res) => {
  req.session.destroy((error) => {
    if (error) {
      console.error("Logout failed:", error);
      return res.redirect("/my/home");
    }

    res.clearCookie("connect.sid");
    return res.redirect("/");
  });
});

async function start() {
  app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
  });

  const ready = await ensureDatabaseReady();

  if (!ready) {
    console.error("Server started without database access. Auth routes will return a database configuration error until PostgreSQL credentials are fixed.");
  }
}

start();
