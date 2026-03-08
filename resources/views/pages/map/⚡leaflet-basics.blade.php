<?php

use Livewire\Component;

new class extends Component
{

public $title;
public $description;
public $author;
public $category;
public $id;
public $importLeafletCSS = '';
public $importLeafletJS = '';
public $setupLeafletDiv = '';
public $setupLeafletMap = '';
public $marker = '';
public $customMarker = '';
public $polyline = '';
public $polygon = '';
public $circle = '';
public $popup = '';

public function returnToMenu()
{
    $this->redirect(route('home'));
}
public function mount()
{
    // Get query parameters from the request
    $this->title = request()->query('title', 'Default Title');
    $this->description = request()->query('description', '');
    $this->author = request()->query('author', 'Unknown');
    $this->category = request()->query('category', 'uncategorized');
    $this->id = request()->query('id');
    $this->importLeafletCSS = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="anonymous" />';
    $this->importLeafletJS = '<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin="anonymous"></script>';
    $this->setupLeafletDiv = '<div id="map" style="width: 100%; height: 500px;"></div>';
    $this->setupLeafletMap =  '
    // Setup Map Leaflet Anda, Pastikan map sesuai dengan yang ada di div.
   let map = L.map("map").setView([-0.9149096, 100.4584898], 17);
   // Pastikan tile layer terhubung dengan benar
   L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
       attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>\'
   }).addTo(map);';
   $this->marker = ' // Buat Marker
   let marker = L.marker([-0.9149096, 100.4584898]).addTo(map);';
   $this->customMarker = '// Buat Custom Marker
   let customMarker = L.marker([-0.9149096, 100.4582], {
       icon: L.icon({
           iconUrl: \'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png\',
           iconSize: [32, 48],
           iconAnchor: [16, 16],
           popupAnchor: [0, -16]
       })
   }).addTo(map);
   customMarker.bindPopup(\'Ini adalah custom marker\'); // Opsional, tambahkan popup jika perlu';
   $this->popup = ' let popup = L.popup()
       .setLatLng([-0.9149096, 100.4582])
       .setContent(\'Ini adalah popup\')
       .openOn(map);';

    $this->polyline = ' // Contoh Polyline
    var polyline = L.polyline([
        [-0.9149096, 100.4584898],
        [-0.9155, 100.4592],
        [-0.9162, 100.4588],
        [-0.9158, 100.4580]
    ], {color: \'red\', weight: 5}).addTo(map);
    polyline.bindPopup(\'Ini adalah polyline\');';
    $this->polygon = ' var polygon = L.polygon([
        [-0.9135, 100.4575],
        [-0.9135, 100.4595],
        [-0.9155, 100.4595],
        [-0.9155, 100.4575]
    ], {color: \'blue\', fillOpacity: 0.3}).addTo(map);
    polygon.bindPopup(\'Ini adalah polygon\');';
    $this->circle = ' var circle = L.circle([-0.9165, 100.4595], {
        color: \'green\',
        fillColor: \'#4caf50\',
        fillOpacity: 0.3,
        radius: 150
    }).addTo(map);
    circle.bindPopup(\'Ini adalah circle\');';

}
};
?>

<div>
    <x-card
        shadow
        class="hover:shadow-xl transition-shadow duration-300 bg-base-100"
        title="{{ $title }}"
        subtitle="Oleh {{ $author }}"
    >
        <div class="mb-3">
            <span class="badge badge-primary badge-sm">{{ $category ?? 'Uncategorized' }}</span>
        </div>
        <div class="text-sm text-base-content/70 line-clamp-3 mb-4">
            {{ $description }}
        </div>

        <x-slot:actions class="justify-end">

            <x-button
                icon="o-arrow-right-end-on-rectangle"
                label="Kembali Ke Menu"
                class="btn-sm btn-ghost"
                wire:click="returnToMenu"
                spinner
            />
        </x-slot:actions>
    </x-card>
    <div class="mb-4"></div>
    <x-card
        shadow
        class="hover:shadow-xl transition-shadow duration-300 bg-base-100"
        title="Pemasangan Leaflet JS"
        subtitle="Setup Proyek"
    >
        <div class="mb-3">
            <p>Langkah pertama adalah menginstal Leaflet JS melalui CDN atau NPM.</p>
            <p> Tambahkan Impor CSS Leaflet ini ke halaman Layout utama Anda. Contohnya pada <code>resources/views/layouts/app.blade.php</code> Di Laravel Livewire.</p>
            <x-code
                wire:model="importLeafletCSS"
                language="html"
                hint="Kode impor CSS Leaflet."
                :readonly="true"
            />
            <p> Tambahkan kode impor JavaScript Leaflet ke halaman Anda. Tambahkan kode berikut ke dalam body halaman layout Anda.</p>
            <x-code
                wire:model="importLeafletJS"

                language="html"
                hint="Kode impor JavaScript Leaflet."
                :readonly="true"
            />
            <p>Tambahkan elemen HTML ini ke dalam body halaman Anda.</p>
            <x-code
                wire:model="setupLeafletDiv"

                language="html"
                hint="Kode HTML untuk elemen map Leaflet."
                :readonly="true"
            />
            <p>Setelah itu, tambahkan kode di bawah ini untuk mengatur map Leaflet Anda.</p>
            <x-code
                wire:model="setupLeafletMap"

                language="javascript"
                hint="Kode pengaturan dasar map Leaflet."
                :readonly="true"
            />
            <p>Hasil Tampilan Setup</p>
            <div id="mapSetup" style="height: 400px; width: 100%;" class="rounded-lg border border-gray-300 z-0"></div>

        </div>


    </x-card>
      <div class="mb-4"></div>
      <x-card
          shadow
          class="hover:shadow-xl transition-shadow duration-300 bg-base-100"
          title="Setup Marker, Custom Marker, dan Popup"
          subtitle="Tahap Pertama"
      >
            <p>Kode untuk penambahan marker pada peta.</p>
            <x-code
                wire:model="marker"

                language="javascript"
                hint="Kode pengaturan marker, custom marker, dan popup."
                :readonly="true"
            />
            <p>Kode penambahan custom marker pada peta.</p>

            <x-code
                wire:model="customMarker"

                language="javascript"
                hint=""
                :readonly="true"
            />
            <p>Kode penambahan popup pada peta.</p>
            <x-code
                wire:model="popup"

                language="javascript"
                hint=""
                :readonly="true"
            />

      </x-card>
    <div class="mb-4"></div>
    <x-card
        shadow
        class="hover:shadow-xl transition-shadow duration-300 bg-base-100"
        title="Penampilan Data Polyline, Polygon, dan Circle"
        subtitle="Tahap Kedua"
    >
        <p>Tampilkan Polyline dengan kode berikut: </p>
        <x-code
            wire:model="polyline"

            language="javascript"
            hint=""
            :readonly="true"
        />
        <p>Tampilkan Polygon dengan kode berikut:</p>
        <x-code
            wire:model="polygon"

            language="javascript"
            hint=""
            :readonly="true"
        />
        <p>Tampilkan Circle dengan kode berikut:</p>
        <x-code
            wire:model="circle"

            language="javascript"
            hint=""
            :readonly="true"
        />

    </x-card>
    <div class="mb-4"></div>
    <x-card
        shadow
        class="hover:shadow-xl transition-shadow duration-300 bg-base-100"
        title="Hasil dari Penampilan Marker, Polyline, Polygon, dan Circle"
        subtitle="Hasil Peta"
    >
        <div id="map" style="height: 400px; width: 100%;" class="rounded-lg border border-gray-300 z-0"></div>
    </x-card>
    <script>
    let mapSetup = L.map('mapSetup').setView([-0.9149096, 100.4584898], 17);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(mapSetup);

    let map = L.map('map').setView([-0.9149096, 100.4584898], 17);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    // Buat Marker
    let marker = L.marker([-0.9149096, 100.4584898]).addTo(map);
    // Buat Custom Marker
    let customMarker = L.marker([-0.9149096, 100.4582], {
        icon: L.icon({
            iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
            iconSize: [32, 48],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16]
        })
    }).addTo(map);
    customMarker.bindPopup('Ini adalah custom marker'); // Opsional, tambahkan popup jika perlu
    let popup = L.popup()
        .setLatLng([-0.9149096, 100.4582])
        .setContent('Ini adalah popup')
        .openOn(map);


    // Contoh Polyline
    var polyline = L.polyline([
        [-0.9149096, 100.4584898],
        [-0.9155, 100.4592],
        [-0.9162, 100.4588],
        [-0.9158, 100.4580]
    ], {color: 'red', weight: 5}).addTo(map);
    polyline.bindPopup('Ini adalah polyline');

    // Contoh Polygon
    var polygon = L.polygon([
        [-0.9135, 100.4575],
        [-0.9135, 100.4595],
        [-0.9155, 100.4595],
        [-0.9155, 100.4575]
    ], {color: 'blue', fillOpacity: 0.3}).addTo(map);
    polygon.bindPopup('Ini adalah polygon');

    // Contoh Circle
    var circle = L.circle([-0.9165, 100.4595], {
        color: 'green',
        fillColor: '#4caf50',
        fillOpacity: 0.3,
        radius: 150
    }).addTo(map);
    circle.bindPopup('Ini adalah circle');
    </script>
</div>
