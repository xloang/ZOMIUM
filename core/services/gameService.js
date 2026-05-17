const { pool } = require("../../db");

async function createGame({ name, description, createdBy }) {
  const result = await pool.query(
    `
      INSERT INTO games (name, description, created_by)
      VALUES ($1, $2, $3)
      RETURNING id, name, description, created_by, created_at
    `,
    [name, description, createdBy],
  );

  return result.rows[0];
}

async function listGames() {
  const result = await pool.query(
    `
      SELECT
        games.id,
        games.name,
        games.description,
        games.created_at,
        users.username AS creator_username
      FROM games
      INNER JOIN users ON users.id = games.created_by
      ORDER BY games.created_at DESC, games.id DESC
    `,
  );

  return result.rows;
}

module.exports = {
  createGame,
  listGames,
};
