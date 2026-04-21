<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireAuth();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="shell">

    <header class="topbar">
        <div class="topbar-brand">
            <div class="topbar-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
            </div>
            <h1><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>
        <nav class="topbar-nav">
            <div id="coin-badge" class="coin-badge" title="Demo Coins remaining">
                <svg class="coin-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M14.5 9a3.5 3.5 0 0 0-5 0 3.5 3.5 0 0 0 0 5 3.5 3.5 0 0 0 5 0"/><path d="M12 6v2"/><path d="M12 16v2"/></svg>
                <span id="coin-count">&#8211;</span>
            </div>
            <a class="btn btn-ghost btn-sm" href="gallery.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Gallery
            </a>
            <a class="btn btn-ghost btn-sm" href="logout.php" title="Sign out">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </nav>
    </header>

    <section class="panel">
        <div class="panel-header">
            <h2>Upload &amp; Analyze</h2>
            <p class="lead">Drop one or multiple product images, optionally add context, and get tags in seconds.</p>
        </div>

        <form id="analyze-form" enctype="multipart/form-data" novalidate>
            <div class="input-tabs">
                <button type="button" id="tab-upload" class="tab-btn active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload Files
                </button>
                <button type="button" id="tab-camera" class="tab-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    Camera
                </button>
            </div>

            <div id="upload-panel">
                <label class="file-drop" id="file-drop-zone" for="image">
                    <input id="image" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                    <svg class="file-drop-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m16 16-4-4-4 4"/></svg>
                    <span class="file-drop-text"><strong>Click to browse</strong> or drag &amp; drop</span>
                    <span class="file-drop-hint">JPG, PNG, or WEBP &middot; up to 5 MB each</span>
                </label>

                <div id="queue-section" class="queue-section hidden">
                    <div class="queue-bar">
                        <span id="queue-count" class="queue-count"></span>
                        <button type="button" id="btn-clear-queue" class="btn btn-ghost btn-sm">Clear all</button>
                    </div>
                    <ul id="queue-list" class="queue-list"></ul>
                </div>
            </div>

            <div id="camera-panel" class="hidden">
                <div class="camera-viewport">
                    <video id="camera-video" autoplay playsinline></video>
                    <canvas id="camera-canvas" class="hidden"></canvas>
                    <img id="camera-preview" class="hidden" alt="Captured photo">
                </div>
                <div class="camera-controls">
                    <button type="button" id="btn-capture" class="btn" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
                        Capture
                    </button>
                    <button type="button" id="btn-retake" class="btn btn-ghost hidden">Retake</button>
                </div>
                <p id="camera-error" class="warn hidden"></p>
            </div>

            <label for="note">Optional note</label>
            <textarea id="note" name="note" rows="2" maxlength="500" placeholder="e.g. winter jacket, focus on material and color"></textarea>

            <button id="submit-btn" class="btn btn-full" type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10"/></svg>
                <span id="submit-label">Analyze</span>
            </button>
        </form>

        <div id="progress-wrap" class="progress-wrap hidden">
            <div class="progress-track"><div id="progress-bar" class="progress-bar"></div></div>
            <div class="progress-status">
                <div class="spinner"></div>
                <span id="progress-text">Preparing...</span>
            </div>
        </div>
    </section>

    <section id="results-section" class="results-section" aria-live="polite"></section>

</main>

<script>
const $ = (s) => document.getElementById(s);

let coinBalance = 0;
const coinCountEl = $('coin-count');
const coinBadge = $('coin-badge');

const form = $('analyze-form');
const imageInput = $('image');
const queueSection = $('queue-section');
const queueList = $('queue-list');
const queueCountEl = $('queue-count');
const btnClearQueue = $('btn-clear-queue');
const fileDropZone = $('file-drop-zone');
const submitBtn = $('submit-btn');
const submitLabel = $('submit-label');
const progressWrap = $('progress-wrap');
const progressBar = $('progress-bar');
const progressText = $('progress-text');
const resultsSection = $('results-section');
const noteInput = $('note');

const tabUpload = $('tab-upload');
const tabCamera = $('tab-camera');
const uploadPanel = $('upload-panel');
const cameraPanel = $('camera-panel');
const video = $('camera-video');
const canvas = $('camera-canvas');
const cameraPreview = $('camera-preview');
const btnCapture = $('btn-capture');
const btnRetake = $('btn-retake');
const cameraError = $('camera-error');

let activeTab = 'upload';
let cameraStream = null;
let capturedBlob = null;
let imageQueue = [];
let isProcessing = false;

async function fetchCoins() {
    try {
        const res = await fetch('api/coins.php');
        const data = await res.json();
        if (data.success) setCoinDisplay(data.data.coins);
    } catch (e) { /* silent */ }
}

function setCoinDisplay(count) {
    coinBalance = count;
    coinCountEl.textContent = count;
    coinBadge.classList.toggle('coin-empty', count <= 0);
    coinBadge.classList.toggle('coin-low', count > 0 && count <= 2);
    updateSubmitState();
}

function updateSubmitState() {
    if (isProcessing) return;
    const noCoins = coinBalance <= 0;
    submitBtn.disabled = noCoins;
    submitBtn.classList.toggle('btn-disabled-coins', noCoins);
}

fetchCoins();

tabUpload.addEventListener('click', () => switchTab('upload'));
tabCamera.addEventListener('click', () => switchTab('camera'));

function switchTab(tab) {
    activeTab = tab;
    tabUpload.classList.toggle('active', tab === 'upload');
    tabCamera.classList.toggle('active', tab === 'camera');
    uploadPanel.classList.toggle('hidden', tab !== 'upload');
    cameraPanel.classList.toggle('hidden', tab !== 'camera');
    if (tab === 'camera') { startCamera(); } else { stopCamera(); }
    updateSubmitLabel();
}

async function startCamera() {
    if (cameraStream) return;
    cameraError.classList.add('hidden');
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 960 } },
            audio: false
        });
        video.srcObject = cameraStream;
        btnCapture.disabled = false;
    } catch (err) {
        cameraError.textContent = 'Camera access denied or unavailable.';
        cameraError.classList.remove('hidden');
        btnCapture.disabled = true;
    }
}

function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(t => t.stop());
        cameraStream = null;
        video.srcObject = null;
    }
    resetCameraUI();
}

function resetCameraUI() {
    capturedBlob = null;
    video.classList.remove('hidden');
    cameraPreview.classList.add('hidden');
    btnCapture.classList.remove('hidden');
    btnRetake.classList.add('hidden');
    btnCapture.disabled = !cameraStream;
    updateSubmitLabel();
}

btnCapture.addEventListener('click', () => {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    canvas.toBlob((blob) => {
        if (!blob) return;
        capturedBlob = blob;
        cameraPreview.src = URL.createObjectURL(blob);
        video.classList.add('hidden');
        cameraPreview.classList.remove('hidden');
        btnCapture.classList.add('hidden');
        btnRetake.classList.remove('hidden');
        updateSubmitLabel();
    }, 'image/jpeg', 0.92);
});

btnRetake.addEventListener('click', () => {
    if (cameraPreview.src) URL.revokeObjectURL(cameraPreview.src);
    resetCameraUI();
    if (!cameraStream) startCamera();
});

imageInput.addEventListener('change', () => {
    if (!imageInput.files) return;
    addFilesToQueue(Array.from(imageInput.files));
    imageInput.value = '';
});

fileDropZone.addEventListener('dragover', (e) => { e.preventDefault(); fileDropZone.classList.add('drag-over'); });
fileDropZone.addEventListener('dragleave', () => fileDropZone.classList.remove('drag-over'));
fileDropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    fileDropZone.classList.remove('drag-over');
    if (e.dataTransfer && e.dataTransfer.files.length) {
        const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
        addFilesToQueue(files);
    }
});

function addFilesToQueue(files) {
    files.forEach((file) => {
        const id = crypto.randomUUID ? crypto.randomUUID() : Date.now() + '-' + Math.random().toString(36).slice(2);
        const url = URL.createObjectURL(file);
        imageQueue.push({ id, file, url, status: 'pending' });
    });
    renderQueue();
}

function removeFromQueue(id) {
    const idx = imageQueue.findIndex(i => i.id === id);
    if (idx === -1) return;
    const item = imageQueue[idx];
    if (item.status === 'processing') return;
    URL.revokeObjectURL(item.url);
    imageQueue.splice(idx, 1);
    renderQueue();
}

btnClearQueue.addEventListener('click', () => {
    imageQueue = imageQueue.filter(i => i.status === 'processing');
    renderQueue();
});

function renderQueue() {
    const pending = imageQueue.filter(i => i.status !== 'done' && i.status !== 'error');
    if (imageQueue.length === 0) {
        queueSection.classList.add('hidden');
        updateSubmitLabel();
        return;
    }
    queueSection.classList.remove('hidden');
    queueCountEl.textContent = imageQueue.length + ' image' + (imageQueue.length !== 1 ? 's' : '') + ' selected';

    queueList.innerHTML = imageQueue.map(item => `
        <li class="queue-item ${item.status}" data-id="${item.id}">
            <img src="${item.url}" alt="">
            ${item.status === 'pending' ? '<button type="button" class="queue-item-remove" aria-label="Remove">&times;</button>' : ''}
        </li>
    `).join('');

    queueList.querySelectorAll('.queue-item-remove').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            removeFromQueue(btn.closest('.queue-item').dataset.id);
        });
    });
    updateSubmitLabel();
}

function updateSubmitLabel() {
    if (isProcessing) return;
    if (coinBalance <= 0) {
        submitLabel.textContent = 'No Coins Left';
        return;
    }
    const count = activeTab === 'camera' ? (capturedBlob ? 1 : 0) : imageQueue.filter(i => i.status === 'pending').length;
    const allowed = Math.min(count, coinBalance);
    if (count > coinBalance) {
        submitLabel.textContent = 'Analyze ' + allowed + ' of ' + count + ' (limited by coins)';
    } else if (count > 1) {
        submitLabel.textContent = 'Analyze ' + count + ' Images';
    } else {
        submitLabel.textContent = 'Analyze';
    }
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (isProcessing) return;

    if (activeTab === 'camera') {
        if (!capturedBlob) return;
        const file = new File([capturedBlob], 'camera-capture.jpg', { type: 'image/jpeg' });
        const id = Date.now().toString();
        const url = URL.createObjectURL(capturedBlob);
        imageQueue.push({ id, file, url, status: 'pending' });
        renderQueue();
        capturedBlob = null;
        resetCameraUI();
    }

    const allPending = imageQueue.filter(i => i.status === 'pending');
    if (allPending.length === 0) return;

    const pending = allPending.slice(0, coinBalance);
    if (pending.length === 0) return;

    isProcessing = true;
    submitBtn.disabled = true;
    submitLabel.textContent = 'Processing...';
    progressWrap.classList.remove('hidden');

    const total = pending.length;
    let completed = 0;

    for (const item of pending) {
        if (coinBalance <= 0) {
            item.status = 'error';
            renderQueue();
            appendErrorCard('No coins remaining.', item.url);
            completed++;
            continue;
        }

        item.status = 'processing';
        renderQueue();
        progressBar.style.width = Math.round((completed / total) * 100) + '%';
        progressText.textContent = 'Analyzing image ' + (completed + 1) + ' of ' + total + '... (' + coinBalance + ' coin' + (coinBalance !== 1 ? 's' : '') + ' left)';

        const formData = new FormData();
        formData.append('image', item.file);
        formData.append('note', noteInput.value);

        try {
            const res = await fetch('api/analyze.php', { method: 'POST', body: formData });
            const payload = await res.json();
            if (!res.ok) throw new Error(payload.error || 'Analysis failed.');
            item.status = 'done';
            if (typeof payload.coins === 'number') setCoinDisplay(payload.coins);
            renderQueue();
            appendResultCard(payload, item.url);
        } catch (err) {
            item.status = 'error';
            renderQueue();
            appendErrorCard(err.message, item.url);
            if (err.message.includes('No coins')) break;
        }

        completed++;
        progressBar.style.width = Math.round((completed / total) * 100) + '%';
    }

    progressText.textContent = 'All done — ' + completed + ' image' + (completed !== 1 ? 's' : '') + ' processed.';
    setTimeout(() => { progressWrap.classList.add('hidden'); }, 2500);

    isProcessing = false;
    submitBtn.disabled = false;
    updateSubmitLabel();
    updateSubmitState();
});

function appendResultCard(payload, localUrl) {
    const info = payload.data || {};
    const tags = Array.isArray(info.tags) ? info.tags : [];
    const imgSrc = info.image_path ? esc(info.image_path) : localUrl;
    const desc = esc(info.description || '');
    const statusHtml = payload.saved
        ? '<span class="result-status ok">Saved</span>'
        : '<span class="result-status warn">Not saved</span>';

    const card = document.createElement('div');
    card.className = 'result-card';
    card.innerHTML = `
        <div class="result-card-inner">
            <div class="result-thumb"><img src="${imgSrc}" alt="Product"></div>
            <div class="result-meta">
                <div>
                    <h3>Tags</h3>
                    <ul class="tag-list">${tags.map(t => '<li>' + esc(String(t)) + '</li>').join('')}</ul>
                </div>
                <div>
                    <h3>Description</h3>
                    <p class="result-desc">${desc}</p>
                </div>
                ${statusHtml}
            </div>
        </div>
    `;
    resultsSection.prepend(card);
}

function appendErrorCard(message, localUrl) {
    const card = document.createElement('div');
    card.className = 'result-card';
    card.innerHTML = `
        <div class="result-card-inner">
            <div class="result-thumb"><img src="${localUrl}" alt="Product"></div>
            <div class="result-meta">
                <span class="result-status err">Failed</span>
                <p class="result-desc">${esc(message)}</p>
            </div>
        </div>
    `;
    resultsSection.prepend(card);
}

function esc(v) {
    return v.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
</script>
<footer class="footer">
    <p>&copy; 2024 Driss Jadidi. <a href="cgu.php">Conditions Générales d'Utilisation</a></p>
</footer>
</body>
</html>
