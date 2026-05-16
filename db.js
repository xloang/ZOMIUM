const { Pool } = require("pg");

const pool = new Pool({
  host: process.env.PGHOST || "127.0.0.1",
  port: Number(process.env.PGPORT || 5432),
  database: process.env.PGDATABASE || "crane",
  user: process.env.PGUSER || "postgres",
  password: process.env.PGPASSWORD || "postgres",
});

async function initializeDatabase() {
  await pool.query(`
    CREATE TABLE IF NOT EXISTS users (
      id BIGSERIAL PRIMARY KEY,
      username VARCHAR(24) NOT NULL,
      username_normalized VARCHAR(24) NOT NULL,
      email VARCHAR(255) NOT NULL,
      email_normalized VARCHAR(255) NOT NULL,
      password_hash TEXT NOT NULL,
      created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
    )
  `);

  await pool.query(`
    CREATE UNIQUE INDEX IF NOT EXISTS users_username_normalized_key
    ON users (username_normalized)
  `);

  await pool.query(`
    CREATE UNIQUE INDEX IF NOT EXISTS users_email_normalized_key
    ON users (email_normalized)
  `);
}

module.exports = {
  pool,
  initializeDatabase,
};
