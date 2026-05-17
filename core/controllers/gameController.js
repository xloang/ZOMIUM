function createGameController({ ensureDatabaseReady, gameService }) {
  function renderDevelopPage(res, options = {}) {
    return res.status(options.statusCode || 200).render("develop", {
      error: options.error || null,
      success: options.success || null,
      formData: options.formData || {},
    });
  }

  async function showDevelopPage(req, res) {
    if (!(await ensureDatabaseReady())) {
      return renderDevelopPage(res, {
        statusCode: 503,
        error: "Database connection is not ready yet. Check PostgreSQL settings and try again.",
      });
    }

    return renderDevelopPage(res, {
      success: req.query.created === "1" ? "Game created successfully." : null,
    });
  }

  async function createGame(req, res) {
    const name = (req.body.name || "").trim();
    const description = (req.body.description || "").trim();

    if (!(await ensureDatabaseReady())) {
      return renderDevelopPage(res, {
        statusCode: 503,
        error: "Database connection is not ready yet. Check PostgreSQL settings and try again.",
        formData: { name, description },
      });
    }

    if (!name || !description) {
      return renderDevelopPage(res, {
        statusCode: 400,
        error: "Game name and description are required.",
        formData: { name, description },
      });
    }

    if (name.length > 100) {
      return renderDevelopPage(res, {
        statusCode: 400,
        error: "Game name must be 100 characters or fewer.",
        formData: { name, description },
      });
    }

    try {
      await gameService.createGame({
        name,
        description,
        createdBy: req.session.user.id,
      });

      return res.redirect("/develop?created=1");
    } catch (error) {
      console.error("Create game failed:", error);
      return renderDevelopPage(res, {
        statusCode: 500,
        error: "Something went wrong while creating the game.",
        formData: { name, description },
      });
    }
  }

  async function showGamesPage(req, res) {
    if (!(await ensureDatabaseReady())) {
      return res.status(503).render("games", {
        games: [],
        error: "Database connection is not ready yet. Check PostgreSQL settings and try again.",
      });
    }

    try {
      const games = await gameService.listGames();

      return res.render("games", {
        games,
        error: null,
      });
    } catch (error) {
      console.error("List games failed:", error);
      return res.status(500).render("games", {
        games: [],
        error: "Something went wrong while loading games.",
      });
    }
  }

  return {
    showDevelopPage,
    createGame,
    showGamesPage,
  };
}

module.exports = {
  createGameController,
};
