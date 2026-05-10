<h1>Foto aufnehmen</h1>

{{ if: $error = session()->take('error') }}
    <div class="notification">
        <p>{{ ==$error }}</p>
    </div>
{{ if; }}

{{ if: $success = session()->take('success') }}
    <div class="notification success">
        <p>{{ ==$success }}</p>
    </div>
{{ if; }}

<div class="camera-container">
    <div class="camera-viewfinder" id="viewfinder">
        <video id="video" autoplay playsinline></video>
        <canvas id="canvas" style="display: none;"></canvas>
        <img id="preview" style="display: none;" alt="Vorschau" />
    </div>

    <div class="camera-controls" id="controls">
        <button type="button" id="captureBtn" class="btn btn-primary">
            <span class="icon">📷</span> Foto aufnehmen
        </button>
        <button type="button" id="retakeBtn" class="btn" style="display: none;">
            <span class="icon">🔄</span> Neu aufnehmen
        </button>
    </div>

    <div class="location-info" id="locationInfo">
        <p><strong>Standort:</strong> <span id="locationStatus">Warte auf GPS...</span></p>
        <div class="row" id="coordsRow" style="display: none;">
            <input type="number" id="lat" name="lat" step="0.000001" placeholder="Breitengrad" />
            <input type="number" id="lon" name="lon" step="0.000001" placeholder="Längengrad" />
        </div>
        <p class="location-hint">
            <small>Die Position wird automatisch erfasst. Du kannst sie oben manuell ändern.</small>
        </p>
    </div>

    <form id="uploadForm" method="post" enctype="multipart/form-data" style="display: none;">
        <input type="hidden" id="imageData" name="image_data" />
        <input type="hidden" id="formLat" name="lat" />
        <input type="hidden" id="formLon" name="lon" />

        <label for="title"><b>Titel</b></label>
        <input type="text" required minlength="2" maxlength="100" name="title" id="title" />

        <label for="author"><b>Autor:in</b></label>
        <input type="text" required minlength="2" maxlength="100" name="author" id="author" />

        <div class="row mt end">
            <a href="/" class="btn">Abbrechen</a>
            <input type="submit" class="btn" value="Speichern" />
        </div>
    </form>
</div>

<style>
    .camera-container {
        max-width: 600px;
        margin: 0 auto;
        padding: 1rem;
    }

    .camera-viewfinder {
        position: relative;
        width: 100%;
        background: #000;
        border-radius: 8px;
        overflow: hidden;
        aspect-ratio: 4/3;
    }

    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #preview {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .camera-controls {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin: 1rem 0;
    }

    .camera-controls .btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-size: 1.1rem;
    }

    .btn-primary {
        background: var(--primary-color, #007bff);
        color: white;
        border: none;
    }

    .location-info {
        background: var(--bg-secondary, #f5f5f5);
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
    }

    .location-info p {
        margin: 0.5rem 0;
    }

    .location-hint {
        color: var(--text-muted, #666);
    }

    .location-status-gps {
        color: green;
        font-weight: bold;
    }

    .location-status-error {
        color: red;
        font-weight: bold;
    }

    .location-status-waiting {
        color: orange;
        font-weight: bold;
    }

    #uploadForm {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px solid var(--border-color, #ddd);
    }
</style>

<script>
    let video = document.getElementById('video');
    let canvas = document.getElementById('canvas');
    let preview = document.getElementById('preview');
    let captureBtn = document.getElementById('captureBtn');
    let retakeBtn = document.getElementById('retakeBtn');
    let uploadForm = document.getElementById('uploadForm');
    let imageDataInput = document.getElementById('imageData');
    let formLatInput = document.getElementById('formLat');
    let formLonInput = document.getElementById('formLon');
    let latInput = document.getElementById('lat');
    let lonInput = document.getElementById('lon');
    let coordsRow = document.getElementById('coordsRow');
    let locationStatus = document.getElementById('locationStatus');

    let currentLat = null;
    let currentLon = null;
    let stream = null;

    async function initCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment',
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                }
            });
            video.srcObject = stream;
        } catch (err) {
            locationStatus.textContent = 'Kamera nicht verfügbar: ' + err.message;
            locationStatus.className = 'location-status-error';
            console.error('Camera error:', err);
        }
    }

    function captureLocation() {
        locationStatus.textContent = 'GPS wird ermittelt...';
        locationStatus.className = 'location-status-waiting';

        if (!navigator.geolocation) {
            locationStatus.textContent = 'GPS wird von diesem Browser nicht unterstützt';
            locationStatus.className = 'location-status-error';
            coordsRow.style.display = 'flex';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                currentLat = position.coords.latitude;
                currentLon = position.coords.longitude;

                latInput.value = currentLat.toFixed(6);
                lonInput.value = currentLon.toFixed(6);
                formLatInput.value = currentLat;
                formLonInput.value = currentLon;

                locationStatus.textContent = `GPS erfasst: ${currentLat.toFixed(4)}, ${currentLon.toFixed(4)}`;
                locationStatus.className = 'location-status-gps';
                coordsRow.style.display = 'flex';
            },
            (error) => {
                let message = 'GPS nicht verfügbar';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        message = 'GPS-Zugriff verweigert. Bitte erlaube den Standortzugriff.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message = 'Standort nicht verfügbar';
                        break;
                    case error.TIMEOUT:
                        message = 'GPS-Timeout. Bitte versuche es erneut.';
                        break;
                }
                locationStatus.textContent = message;
                locationStatus.className = 'location-status-error';
                coordsRow.style.display = 'flex';
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    }

    function capturePhoto() {
        if (!stream) {
            alert('Kamera nicht verfügbar');
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        let ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        let dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        preview.src = dataUrl;
        imageDataInput.value = dataUrl;

        video.style.display = 'none';
        preview.style.display = 'block';
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'inline-block';
        uploadForm.style.display = 'block';

        formLatInput.value = currentLat || '';
        formLonInput.value = currentLon || '';
    }

    function retakePhoto() {
        preview.style.display = 'none';
        video.style.display = 'block';
        captureBtn.style.display = 'inline-block';
        retakeBtn.style.display = 'none';
        uploadForm.style.display = 'none';
        imageDataInput.value = '';
    }

    latInput.addEventListener('input', function() {
        formLatInput.value = this.value;
    });

    lonInput.addEventListener('input', function() {
        formLonInput.value = this.value;
    });

    captureBtn.addEventListener('click', function() {
        captureLocation();
        setTimeout(capturePhoto, 500);
    });

    retakeBtn.addEventListener('click', retakePhoto);

    uploadForm.addEventListener('submit', function(e) {
        if (!imageDataInput.value) {
            e.preventDefault();
            alert('Bitte zuerst ein Foto aufnehmen');
            return;
        }

        if (!formLatInput.value || !formLonInput.value) {
            let confirmUpload = confirm('Keine GPS-Koordinaten verfügbar. Foto trotzdem hochladen? Du musst dann manuell die Position auf der Karte wählen.');
            if (!confirmUpload) {
                e.preventDefault();
                return;
            }
        }
    });

    initCamera();
</script>
