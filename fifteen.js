/* 
fifteen.js 
Main logic for the Fifteen Puzzle game. 
Implements: 
1. Puzzle initialization and layout 
2. Tile movement and multi-tile sliding 
3. Tile shuffling with solvability 
4. Hover effects and move validation 
5. Win detection and message 
6. Background and puzzle size selectors 
*/ 

// Constants
const PUZZLE_AREA = document.getElementById('puzzlearea');
const PUZZLE_CONTAINER_SIZE = 400; // fixed container width & height
let TILE_SIZE = 100; // will be recalculated dynamically

let tiles = [];
let emptyX = 3;
let emptyY = 3;
let size = 4; // default 4x4
let backgroundImage = "background.jpg";

// On page load
window.addEventListener('load', () => {
    document.getElementById("shufflebutton").addEventListener("click", shufflePuzzle);
    document.getElementById("bg-select").addEventListener("change", changeBackground);
    document.getElementById("size-select").addEventListener("change", changeSize);
    document.getElementById("solve-button").addEventListener("click", solvePuzzle);
    init();
});

// Initialize board
function init() {
    TILE_SIZE = PUZZLE_CONTAINER_SIZE / size;  // recalc tile size
    PUZZLE_AREA.style.width = `${PUZZLE_CONTAINER_SIZE}px`;
    PUZZLE_AREA.style.height = `${PUZZLE_CONTAINER_SIZE}px`;
    PUZZLE_AREA.innerHTML = "";
    tiles = [];

    for (let row = 0; row < size; row++) {
        for (let col = 0; col < size; col++) {
            let number = row * size + col + 1;
            if (number === size * size) break; // last square is empty

            const tile = document.createElement("div");
            tile.className = "tile";
            tile.innerText = number;

            tile.style.width = `${TILE_SIZE}px`;
            tile.style.height = `${TILE_SIZE}px`;
            tile.style.lineHeight = `${TILE_SIZE}px`;
            tile.style.top = `${row * TILE_SIZE}px`;
            tile.style.left = `${col * TILE_SIZE}px`;
            tile.style.backgroundPosition = `-${col * TILE_SIZE}px -${row * TILE_SIZE}px`;
            tile.style.backgroundImage = `url(${backgroundImage})`;

            tile.dataset.x = col;
            tile.dataset.y = row;

            tile.addEventListener("click", () => moveTile(tile));
            tile.addEventListener("mouseover", () => highlight(tile));
            tile.addEventListener("mouseout", () => unhighlight(tile));

            PUZZLE_AREA.appendChild(tile);
            tiles.push(tile);
        }
    }
    emptyX = size - 1;
    emptyY = size - 1;
    document.getElementById("win-message").textContent = "";
    PUZZLE_AREA.style.border = "4px solid #f4efef"; // reset border on init
}



// Move a tile or chain of tiles if valid
function moveTile(tile) {
    const x = parseInt(tile.dataset.x);
    const y = parseInt(tile.dataset.y);

    // Move horizontal line
    if (y === emptyY && x !== emptyX) {
        const dx = x < emptyX ? 1 : -1;
        for (let i = emptyX - dx; i !== x - dx; i -= dx) {
            movePiece(getTileAt(i, y));
        }
    }
    // Move vertical line
    else if (x === emptyX && y !== emptyY) {
        const dy = y < emptyY ? 1 : -1;
        for (let i = emptyY - dy; i !== y - dy; i -= dy) {
            movePiece(getTileAt(x, i));
        }
    }

    checkWin();
}

// Move a single tile into the empty spot
function movePiece(tile) {
    if (!tile) return;
    const x = parseInt(tile.dataset.x);
    const y = parseInt(tile.dataset.y);

    tile.style.left = `${emptyX * TILE_SIZE}px`;
    tile.style.top = `${emptyY * TILE_SIZE}px`;
    tile.dataset.x = emptyX;
    tile.dataset.y = emptyY;

    emptyX = x;
    emptyY = y;
}

// Highlight tile on hover if movable
function highlight(tile) {
    const x = parseInt(tile.dataset.x);
    const y = parseInt(tile.dataset.y);
    if (x === emptyX || y === emptyY) {
        tile.classList.add("movablepiece");
    }
}

// Remove highlight
function unhighlight(tile) {
    tile.classList.remove("movablepiece");
}

// Get tile at specific (x, y)
function getTileAt(x, y) {
    return tiles.find(t => parseInt(t.dataset.x) === x && parseInt(t.dataset.y) === y);
}

// Shuffle puzzle by random valid moves
function shufflePuzzle() {
    let moves = 300;
    while (moves > 0) {
        let neighbors = getMovableNeighbors();
        const tile = neighbors[Math.floor(Math.random() * neighbors.length)];
        movePiece(tile);
        moves--;
    }
    document.getElementById("win-message").textContent = "";
    PUZZLE_AREA.style.border = "4px solid #f4efef"; // reset border on shuffle
}

// Get valid neighbors for shuffle
function getMovableNeighbors() {
    let list = [];
    for (let tile of tiles) {
        const x = parseInt(tile.dataset.x);
        const y = parseInt(tile.dataset.y);
        if (x === emptyX || y === emptyY) list.push(tile);
    }
    return list;
}

// Check if tiles are in order (win state)
function checkWin() {
    let correct = true;
    for (let tile of tiles) {
        let x = parseInt(tile.dataset.x);
        let y = parseInt(tile.dataset.y);
        let expected = y * size + x + 1;
        if (parseInt(tile.innerText) !== expected) {
            correct = false;
            break;
        }
    }
    if (correct) {
        document.getElementById("win-message").textContent = "🎉 You solved the puzzle!";
        PUZZLE_AREA.style.border = "5px solid green";
    }
}

// Background change handler
function changeBackground() {
    backgroundImage = this.value;
    for (let tile of tiles) {
        let x = parseInt(tile.dataset.x);
        let y = parseInt(tile.dataset.y);
        tile.style.backgroundImage = `url(${backgroundImage})`;
        tile.style.backgroundPosition = `-${x * TILE_SIZE}px -${y * TILE_SIZE}px`;
    }
}

// Puzzle size change handler
function changeSize() {
    size = parseInt(this.value);
    TILE_SIZE = PUZZLE_CONTAINER_SIZE / size;
    init();
}

// Cheat function to solve the puzzle instantly
function solvePuzzle() {
    tiles.forEach((tile, index) => {
        const x = index % size;
        const y = Math.floor(index / size);

        tile.style.left = `${x * TILE_SIZE}px`;
        tile.style.top = `${y * TILE_SIZE}px`;
        tile.dataset.x = x;
        tile.dataset.y = y;
        tile.innerText = index + 1;
        tile.style.display = "block"; // ensure visible
        tile.style.width = `${TILE_SIZE}px`;
        tile.style.height = `${TILE_SIZE}px`;
        tile.style.lineHeight = `${TILE_SIZE}px`;
    });

    // Hide last tile (empty space)
tiles.forEach(tile => tile.style.display = 'block');

    // Update empty position to bottom right
    emptyX = size - 1;
    emptyY = size - 1;

    // Show win message
    document.getElementById("win-message").textContent = "🎉 Cheat activated! Puzzle solved.";
    PUZZLE_AREA.style.border = "5px solid green";
}

const selectedImage = document.getElementById('bg-select').value;
const imagePath = 'uploads/backgrounds/' + selectedImage;
// Use imagePath to set the background

