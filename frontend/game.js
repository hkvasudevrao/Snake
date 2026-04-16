(() => {
  const API_BASE = window.__API_BASE__ || "/api";
  const GRID_SIZE = 20;
  const CELL = 20;
  const TICK_MS = 110;

  const canvas = document.getElementById("game");
  const ctx = canvas.getContext("2d");
  const scoreEl = document.getElementById("score");
  const statusEl = document.getElementById("status");
  const leaderboardEl = document.getElementById("leaderboard");
  const sessionEl = document.getElementById("session");

  const startBtn = document.getElementById("start-btn");
  const resetBtn = document.getElementById("reset-btn");
  const registerBtn = document.getElementById("register-btn");
  const loginBtn = document.getElementById("login-btn");
  const refreshBoardBtn = document.getElementById("refresh-board-btn");

  const usernameInput = document.getElementById("username");
  const passwordInput = document.getElementById("password");

  let snake = [];
  let direction = { x: 1, y: 0 };
  let nextDirection = { x: 1, y: 0 };
  let food = { x: 10, y: 10 };
  let score = 0;
  let gameTimer = null;
  let token = localStorage.getItem("snake_token") || "";
  let currentUser = localStorage.getItem("snake_user") || "";

  function setStatus(message, isError = false) {
    statusEl.textContent = message;
    statusEl.classList.toggle("error", isError);
  }

  function updateSession() {
    sessionEl.textContent = currentUser ? `Logged in as ${currentUser}` : "Not logged in";
  }

  function resetGameState() {
    snake = [
      { x: 8, y: 10 },
      { x: 7, y: 10 },
      { x: 6, y: 10 }
    ];
    direction = { x: 1, y: 0 };
    nextDirection = { x: 1, y: 0 };
    score = 0;
    scoreEl.textContent = String(score);
    food = spawnFood();
    render();
  }

  function spawnFood() {
    while (true) {
      const candidate = {
        x: Math.floor(Math.random() * GRID_SIZE),
        y: Math.floor(Math.random() * GRID_SIZE)
      };
      const overlap = snake.some((part) => part.x === candidate.x && part.y === candidate.y);
      if (!overlap) {
        return candidate;
      }
    }
  }

  function drawCell(x, y, color) {
    ctx.fillStyle = color;
    ctx.fillRect(x * CELL, y * CELL, CELL, CELL);
  }

  function render() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    drawCell(food.x, food.y, "#f08a24");

    snake.forEach((part, index) => {
      const color = index === 0 ? "#12776b" : "#1f9a8f";
      drawCell(part.x, part.y, color);
    });
  }

  function collides(node) {
    if (node.x < 0 || node.y < 0 || node.x >= GRID_SIZE || node.y >= GRID_SIZE) {
      return true;
    }

    return snake.some((part) => part.x === node.x && part.y === node.y);
  }

  async function handleGameOver() {
    stopGameLoop();
    setStatus("Game over", true);

    if (!token) {
      return;
    }

    try {
      await apiFetch("/submit_score.php", {
        method: "POST",
        body: JSON.stringify({ score })
      }, true);
      await loadLeaderboard();
      setStatus("Score submitted");
    } catch (error) {
      setStatus(error.message, true);
    }
  }

  function step() {
    direction = nextDirection;
    const head = { x: snake[0].x + direction.x, y: snake[0].y + direction.y };

    if (collides(head)) {
      void handleGameOver();
      return;
    }

    snake.unshift(head);

    if (head.x === food.x && head.y === food.y) {
      score += 10;
      scoreEl.textContent = String(score);
      food = spawnFood();
    } else {
      snake.pop();
    }

    render();
  }

  function startGameLoop() {
    if (gameTimer) {
      return;
    }
    setStatus("Running");
    gameTimer = setInterval(step, TICK_MS);
  }

  function stopGameLoop() {
    if (gameTimer) {
      clearInterval(gameTimer);
      gameTimer = null;
    }
  }

  function parseDirection(key) {
    const map = {
      ArrowUp: { x: 0, y: -1 },
      ArrowDown: { x: 0, y: 1 },
      ArrowLeft: { x: -1, y: 0 },
      ArrowRight: { x: 1, y: 0 },
      w: { x: 0, y: -1 },
      s: { x: 0, y: 1 },
      a: { x: -1, y: 0 },
      d: { x: 1, y: 0 }
    };

    return map[key] || null;
  }

  function setDirection(candidate) {
    if (!candidate) {
      return;
    }

    if (candidate.x === -direction.x && candidate.y === -direction.y) {
      return;
    }

    nextDirection = candidate;
  }

  async function apiFetch(path, options = {}, withAuth = false) {
    const headers = {
      "Content-Type": "application/json",
      ...(options.headers || {})
    };

    if (withAuth && token) {
      headers.Authorization = `Bearer ${token}`;
    }

    const response = await fetch(`${API_BASE}${path}`, {
      ...options,
      headers
    });

    let payload = null;
    const contentType = response.headers.get("content-type") || "";
    if (contentType.includes("application/json")) {
      payload = await response.json();
    }

    if (!response.ok) {
      const message = (payload && payload.error) || `Request failed with status ${response.status}`;
      throw new Error(message);
    }

    return payload;
  }

  function usernameAndPassword() {
    const username = usernameInput.value.trim();
    const password = passwordInput.value;
    return { username, password };
  }

  async function register() {
    const creds = usernameAndPassword();
    if (!creds.username || !creds.password) {
      setStatus("Username and password required", true);
      return;
    }

    try {
      await apiFetch("/register.php", {
        method: "POST",
        body: JSON.stringify(creds)
      });
      setStatus("Registered successfully. You can now login.");
    } catch (error) {
      setStatus(error.message, true);
    }
  }

  async function login() {
    const creds = usernameAndPassword();
    if (!creds.username || !creds.password) {
      setStatus("Username and password required", true);
      return;
    }

    try {
      const payload = await apiFetch("/login.php", {
        method: "POST",
        body: JSON.stringify(creds)
      });

      token = payload.token;
      currentUser = payload.username;
      localStorage.setItem("snake_token", token);
      localStorage.setItem("snake_user", currentUser);
      updateSession();
      setStatus("Login successful");
    } catch (error) {
      setStatus(error.message, true);
    }
  }

  async function loadLeaderboard() {
    try {
      const payload = await apiFetch("/leaderboard.php?limit=10", { method: "GET" });
      leaderboardEl.innerHTML = "";

      payload.leaderboard.forEach((entry) => {
        const item = document.createElement("li");
        item.textContent = `${entry.username}: ${entry.best_score}`;
        leaderboardEl.appendChild(item);
      });

      if (payload.leaderboard.length === 0) {
        const item = document.createElement("li");
        item.textContent = "No scores yet";
        leaderboardEl.appendChild(item);
      }
    } catch (error) {
      setStatus(error.message, true);
    }
  }

  startBtn.addEventListener("click", () => {
    if (!gameTimer) {
      startGameLoop();
    }
  });

  resetBtn.addEventListener("click", () => {
    stopGameLoop();
    resetGameState();
    setStatus("Ready");
  });

  registerBtn.addEventListener("click", () => {
    void register();
  });

  loginBtn.addEventListener("click", () => {
    void login();
  });

  refreshBoardBtn.addEventListener("click", () => {
    void loadLeaderboard();
  });

  window.addEventListener("keydown", (event) => {
    const key = event.key.length === 1 ? event.key.toLowerCase() : event.key;
    setDirection(parseDirection(key));
  });

  resetGameState();
  updateSession();
  void loadLeaderboard();
})();
