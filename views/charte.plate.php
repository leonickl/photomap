<div id="map"></div>

<a id="add-btn" href="/create">+</a>

<style>
    #map {
        height: 100vh;
        width: 100vw;
    }

    #add-btn, #add-btn:visited {
        position: fixed;
        top: 1rem;
        right: 1rem;
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
                    <img src="img/{{ $marker->file }}" style="max-width: 100%; max-height: 100%"/>`);
        {{ if; }}
    {{ each; }}
</script>