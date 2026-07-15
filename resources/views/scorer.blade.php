<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bloxo Scorer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-black text-white antialiased">
    <header class="relative flex items-center justify-center px-6 py-5">
        <button
            id="restartGameBtn"
            class="absolute left-5 hidden h-11 w-11 items-center justify-center rounded-2xl border border-white/25 bg-[#111111] text-lg font-black text-white transition hover:border-white"
            type="button"
            onclick="restartGame()"
            aria-label="Restart scorer"
        >
            ↻
        </button>

        <img src="{{ asset('images/bloxo_logo.png') }}" alt="Bloxo" class="h-12 w-auto object-contain">

        <button
            class="absolute right-5 flex h-11 w-11 items-center justify-center rounded-2xl border border-white/25 bg-[#111111] text-2xl font-black text-white transition hover:border-white"
            type="button"
            onclick="openAddPlayerModal()"
            aria-label="Add player"
        >
            +
        </button>
    </header>

    <main class="min-h-[calc(100vh-84px)] px-5 pb-10 pt-8">
        <section class="mx-auto flex max-w-3xl flex-col items-center">
            <div id="emptyState" class="mt-16 flex flex-col items-center text-center">
                <p class="text-2xl font-extrabold tracking-tight">Tap + to add players.</p>
                <p class="mt-3 text-lg font-semibold text-white/70">Swipe left or use delete to remove players.</p>
            </div>

            <div id="playersContainer" class="mt-10 hidden w-full space-y-4"></div>
        </section>
    </main>

    <div id="addPlayerModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-5 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-3xl border border-white/20 bg-[#111111] p-6 shadow-2xl">
            <h2 class="text-2xl font-extrabold">Add Player</h2>
            <input
                type="text"
                id="playerNameInput"
                placeholder="Player name"
                class="mt-5 w-full border-0 border-b border-white/25 bg-transparent px-1 py-3 text-lg text-white outline-none placeholder:text-white/35 focus:border-[#2196F3] focus:ring-0"
            >
            <div class="mt-6 flex justify-end gap-3">
                <button class="rounded-2xl border border-white/20 px-5 py-3 font-bold text-white/80" type="button" onclick="closeModal()">Cancel</button>
                <button class="rounded-2xl border border-white/70 bg-[#0E1724] px-6 py-3 font-extrabold text-white" type="button" onclick="addPlayer()">Add</button>
            </div>
        </div>
    </div>

    <div id="confirmDeleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-5 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-3xl border border-white/20 bg-[#111111] p-6 shadow-2xl">
            <h2 class="text-2xl font-extrabold">Remove Player</h2>
            <p id="deletePlayerName" class="mt-4 text-white/70"></p>
            <div class="mt-6 flex justify-end gap-3">
                <button class="rounded-2xl border border-white/20 px-5 py-3 font-bold text-white/80" type="button" onclick="closeDeleteModal()">Cancel</button>
                <button class="rounded-2xl border border-white/70 bg-[#0E1724] px-6 py-3 font-extrabold text-white" type="button" onclick="removeConfirmed()">Remove</button>
            </div>
        </div>
    </div>

    <script>
        let players = JSON.parse(localStorage.getItem('bloxoScorePlayers')) || [];
        let playerToRemove = null;

        document.addEventListener('DOMContentLoaded', () => {
            renderPlayers();

            const input = document.getElementById('playerNameInput');
            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') addPlayer();
            });
        });

        function openAddPlayerModal() {
            const modal = document.getElementById('addPlayerModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => document.getElementById('playerNameInput').focus(), 30);
        }

        function closeModal() {
            document.getElementById('playerNameInput').value = '';
            document.getElementById('addPlayerModal').classList.add('hidden');
            document.getElementById('addPlayerModal').classList.remove('flex');
        }

        function closeDeleteModal() {
            playerToRemove = null;
            document.getElementById('confirmDeleteModal').classList.add('hidden');
            document.getElementById('confirmDeleteModal').classList.remove('flex');
        }

        function savePlayers() {
            localStorage.setItem('bloxoScorePlayers', JSON.stringify(players));
        }

        function addPlayer() {
            const input = document.getElementById('playerNameInput');
            const name = input.value.trim();
            if (!name) return;

            players.push({
                id: Date.now() + Math.random(),
                name,
                score: 0,
            });

            savePlayers();
            renderPlayers();
            closeModal();
        }

        function updateScore(index, value) {
            players[index].score += value;
            savePlayers();
            renderPlayers();
        }

        function confirmRemove(index) {
            playerToRemove = index;
            document.getElementById('deletePlayerName').textContent = `Remove ${players[index].name}?`;
            document.getElementById('confirmDeleteModal').classList.remove('hidden');
            document.getElementById('confirmDeleteModal').classList.add('flex');
        }

        function removeConfirmed() {
            if (playerToRemove === null) return;

            players.splice(playerToRemove, 1);
            savePlayers();
            renderPlayers();
            closeDeleteModal();
        }

        function restartGame() {
            if (!players.length) return;
            if (!confirm('Restart the scoring app and remove all players?')) return;

            players = [];
            savePlayers();
            renderPlayers();
        }

        function renderPlayers() {
            const container = document.getElementById('playersContainer');
            const emptyState = document.getElementById('emptyState');
            const restartButton = document.getElementById('restartGameBtn');
            container.innerHTML = '';

            if (!players.length) {
                emptyState.classList.remove('hidden');
                container.classList.add('hidden');
                restartButton.classList.add('hidden');
                restartButton.classList.remove('flex');
                return;
            }

            emptyState.classList.add('hidden');
            container.classList.remove('hidden');
            restartButton.classList.remove('hidden');
            restartButton.classList.add('flex');

            players.forEach((player, index) => {
                const row = document.createElement('article');
                row.className = 'rounded-3xl border border-white/75 bg-[#0E1724] p-4 shadow-[0_16px_40px_rgba(0,0,0,0.35)]';

                row.innerHTML = `
                    <div class="flex items-center gap-4">
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-xl font-extrabold">${escapeHtml(player.name)}</h2>
                        </div>
                        <div class="text-5xl font-black leading-none">${player.score}</div>
                        <button
                            aria-label="Remove ${escapeHtml(player.name)}"
                            class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/25 text-lg font-black text-white/80 transition hover:border-white hover:text-white"
                            type="button"
                            onclick="confirmRemove(${index})"
                        >
                            ×
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-4 gap-3">
                        ${scoreButton(index, -5)}
                        ${scoreButton(index, -1)}
                        ${scoreButton(index, 1)}
                        ${scoreButton(index, 5)}
                    </div>
                `;

                container.appendChild(row);
            });
        }

        function scoreButton(index, value) {
            const label = value > 0 ? `+${value}` : `${value}`;
            return `
                <button
                    class="h-14 rounded-2xl border border-white/75 bg-[#111111] text-base font-black text-white transition hover:bg-white hover:text-black active:scale-95"
                    type="button"
                    onclick="updateScore(${index}, ${value})"
                >
                    ${label}
                </button>
            `;
        }

        function escapeHtml(value) {
            return value
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }
    </script>
</body>
</html>
