<h1>Neues Foto hochladen</h1>

{{ if: $error = session()->take('error') }}
    <div class="notification">
        <p>{{ ==$error }}</p>
    </div>
{{ if; }}

<form action="/" method="post" enctype="multipart/form-data">
    <label for="title"><b>Titel</b></label>
    <input type="text" required minlength="2" maxlength="100" name="title" id="title" value="{{ session()->take('title') }}" />

    <label for="author"><b>Autor:in</b></label>
    <input type="text" required minlength="2" maxlength="100" name="author" id="author" value="{{ session()->take('author') }}" />

    <label for="photo"><b>Foto</b></label>
    <div class="file-input-wrapper">
        <div class="drop-zone" id="dropZone" onclick="document.getElementById('photo').click()">
            <input type="file" id="photo" name="photo" required accept="*/*,.jpg,.jpeg" />
            <div class="drop-zone-text" id="dropText">
                Foto wählen oder hierher ziehen <span>JPG bis 10 MB</span>
            </div>
        </div>
    </div>

    <p class="mt">Das Bild möglichst nicht mit dem Google-Foto-Picker,
        sondern am besten <span class="highlight">über "Dateien" oder so hochladen</span>, sonst
        kann es sein, dass die Koordinaten zurückgesetzt werden.
        Oder am Laptop, da sollte es immer klappen.</p>

    <div class="row mt end">
        <a href="/" class="btn">Zurück</a>
        <input type="submit" class="btn" value="Speichern" />
    </div>
</form>
