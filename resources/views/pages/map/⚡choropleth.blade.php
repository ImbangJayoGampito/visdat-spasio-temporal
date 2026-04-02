<?php

use Livewire\Component;
use Illuminate\Support\Facades\File;
new class extends Component {
    public string $title;
    public string $description;
    public string $author;
    public string $category;
    public ?string $id;
    public $csv_result;
    public $geojson;

    public function selectAllProvinces()
    {
        $this->provincesSelected = array_keys($this->provinceDicts);

        $this->dispatch(
            "provincesSelected",
            provinces: $this->provincesSelected,
        );
    }

    // Optional: Add clear all method
    public function clearAllProvinces()
    {
        $this->provincesSelected = [];
        $this->dispatch(
            "provincesSelected",
            provinces: $this->provincesSelected,
        );
    }
    public function returnToMenu()
    {
        $this->redirect(route("home"));
    }
    function updatedDetailProvinsi($value)
    {
        $this->dispatch("detailProvinsiChanged", detailProvinsi: $value);
    }
    public function updatedYearChanged($value)
    {
        $this->chosenYear = $value;
        $this->dispatch("yearChanged", year: $value);
    }
    public function toggleProvince($code)
    {
        $this->dispatch(
            "provincesSelected",
            provinces: $this->provincesSelected,
        );
    }
    public function loadGeoJson()
    {
        $geojsonPath = public_path("west_sumatra.geojson");
        $content = File::get($geojsonPath);
        $data = json_decode($content, true);
        $this->geojson = $data;
        $this->dispatch("loadGeoJSON", geojsonData: $this->geojson);
    }
    public function mount()
    {
        $csv_file = public_path("datasets/data_processed.csv");
        $this->loadGeoJSON();

        $this->title = request()->query("title", "Default Title");
        $this->description = request()->query("description", "");
        $this->author = request()->query("author", "Unknown");
        $this->category = request()->query("category", "uncategorized");
        $this->id = request()->query("id");

        // Process the csv file first
        if (file_exists($csv_file)) {
            $csv = file_get_contents($csv_file);
            $rows = explode("\n", $csv);
            $headers = str_getcsv(array_shift($rows));
            $data = [];
            foreach ($rows as $row) {
                if (trim($row)) {
                    $data[] = array_combine($headers, str_getcsv($row));
                }
            }
            $this->csv_result = [
                "headers" => $headers,
                "data" => $data,
                "total_rows" => count($data),
            ];
            foreach ($data as $row) {
            }
        } else {
            $this->csv_result = null;
        }
    }
};
?>

<div>
    {{-- Live as if you were to die tomorrow. Learn as if you were to live forever. - Mahatma Gandhi --}}

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

         <div id="csvContent" class="mt-4 p-4 bg-gray-100 rounded"></div>
    </x-card>

    <x-card
        shadow
        class="hover:shadow-xl transition-shadow duration-300 bg-base-100"
        title="Choropleth Map"
        subtitle="Peta Kemiskinan Provinsi/Kabupaten"
    >

        <!-- DEBUG SECTION - Add this temporarily -->
        <div id="map" style="height: 400px; width: 100%;" class="rounded-lg border border-gray-300 z-0"></div>





    </x-card>
    <script>
    var geojson;

    document.addEventListener('DOMContentLoaded', function() {
        loadCsvContent();






    async function loadCsvContent() {
        const csvData = @json($csv_result);
            console.log(csvData);
        let data = csvData.data;
        let headers = csvData.headers;
        console.log(csvData);
    }

    document.addEventListener('livewire:init', function() {
                Livewire.on('loadGeoJSON', (event) => {

                    const geojsonData = event.geojsonData;
                    initializeMap(geojsonData);
                });
            });



    });
    var map;
    function initializeMap(geojsonData) {
                // Check if map already exists


                // Initialize map
                var map= L.map('map').setView([-0.5, 100.5], 8);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                console.log(geojsonData)
                // Add GeoJSON data
                const geoJsonLayer = L.geoJSON(geojsonData, {
                    style: {
                        color: '#3388ff',
                        weight: 2,
                        fillColor: '#3388ff',
                        fillOpacity: 0.5
                    },
                    onEachFeature: function(feature, layer) {
                        if (feature.properties && feature.properties.NAME_2_fixed) {
                            layer.bindPopup(`
                                <strong>${feature.properties.NAME_2_fixed}</strong><br>
                                Type: ${feature.properties.TYPE_2 || 'N/A'}<br>
                                Country: ${feature.properties.COUNTRY || 'Indonesia'}
                            `);
                        }
                    }
                }).addTo(map);

                // Fit bounds
                try {
                    map.fitBounds(geoJsonLayer.getBounds());
                } catch(e) {
                    console.warn('Could not fit bounds:', e);
                }
            }

    </script>
</div>
