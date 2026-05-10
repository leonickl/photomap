<div id="map"></div>

<a id="add-btn" href="/create" title="Foto hochladen">+</a>
<a id="camera-btn" href="/camera" title="Foto aufnehmen">📷</a>

<style>
    #map {
        height: 100vh;
        width: 100vw;
    }

    #add-btn, #add-btn:visited {
        position: fixed;
        top: 1rem;
        right: 5rem;
        background: blue;
        height: 3rem;
        width: 3rem;
        z-index: 1000;
        color: white;
        border-radius: 50%;
        font-size: 35px;
        font-weight: bolder;
        border: 2px white solid;
        text-decoration: none;
        text-align: center;
    }

    #camera-btn, #camera-btn:visited {
        position: fixed;
        top: 1rem;
        right: 1rem;
        background: #28a745;
        height: 3rem;
        width: 3rem;
        z-index: 1000;
        color: white;
        border-radius: 50%;
        font-size: 28px;
        font-weight: bolder;
        border: 2px white solid;
        text-decoration: none;
        text-align: center;
        line-height: 2.8rem;
    }

    .open-image {
        display: flex;
        justify-content: center;
    }

    .open-image a {
        background: lightblue;
        padding: 5px 20px;
        border-radius: 5px;
        border: 1px #56afcd solid;
        color: black;
        text-decoration: underline;
    }
</style>

<script>
    const map = L.map('map').setView([48.6, 13.4], 5.16); // Europe with Passau as center

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    {{ each: $markers as $marker }}
        {{ if: $position = $marker->position() }}
            L.marker({{ json_encode($position) }}).addTo(map)
                .bindPopup(`<b>{{ $marker->title }}</b>
                    <em>{{ $marker->author }}</em>
                    <img src="img/{{ $marker->file }}" style="max-width: 100%; max-height: 100%"/>
                    <div class="open-image"><a href="img/{{ $marker->file }}" target="_blank">Öffnen</a></div>`);
        {{ if; }}
    {{ each; }}

    const popup = L.popup();

    function onMapClick(e) {
        popup
            .setLatLng(e.latlng)
            .setContent(`Möchtest du an dieser Stelle hier ein Bild hochladen?
                Bitte wähle die Position <span="highlight">möglichst genau</span> aus.
                <a href="/create?pos=${e.latlng}" class="btn">Hochladen</a>`)
            .openOn(map);
    }

    map.on('click', onMapClick);
</script>