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
        subtitle="Peta informatif"
    >

        <div class="flex flex-col gap-1">
            <label for="indicator-select" class="text-sm font-medium ">Indikator</label>
            <select id="indicator-select" class="px-4 py-2 pr-8 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm cursor-pointer hover:border-gray-400 transition-colors">
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label for="year-select" class="text-sm font-medium ">Tahun</label>
            <select id="year-select" class="px-4 py-2 pr-8 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm cursor-pointer hover:border-gray-400 transition-colors">
            </select>
        </div>
        <div id="map" style="height: 400px; width: 100%;" class="rounded-lg border border-gray-300 z-0"></div>

        <div id="hover-info" style="position: absolute; bottom: 20px; left: 20px; background: rgba(0,0,0,0.7); color: white; padding: 8px 12px; border-radius: 5px; font-size: 12px; z-index: 1000; pointer-events: none;">
            Hover over regions for details
        </div>

        <div id="legend" style="position: absolute; bottom: 20px; right: 20px; background: white; padding: 10px 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1000; font-size: 12px; min-width: 150px;">
            <h4 style="margin: 0 0 8px 0;">Legend</h4>
            <div id="legend-content">Loading...</div>
        </div>



    </x-card>
    <script>
        var map;
        var currentLayer;
        var geojsonData;

        const config = {
            categories: {
                'Umur Harapan Hidup (Tahun)': 'UHH (Tahun)',
                'Harapan Lama Sekolah (Tahun)': 'HLS (Tahun)',
                'Rata-rata Lama Sekolah (Tahun)': 'RLS (Tahun)',
                'Pengeluaran (Ribu rupiah/orang/tahun)': 'Pengeluaran (Ribu Rp/orang/tahun)',
                'IPM': 'IPM'
            },
            years: Array.from({length: 11}, (_, i) => (2015 + i).toString()),
            defaultCategory: 'IPM',
            defaultYear: '2024',
            selectedCategory: 'IPM',
            selectedYear: '2024'
        };

        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('indicator-select');
            Object.entries(config.categories).forEach(([key, label]) => {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = label;
                if (key === config.selectedCategory) option.selected = true;
                categorySelect.appendChild(option);
            });

            const yearSelect = document.getElementById('year-select');
            config.years.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === config.selectedYear) option.selected = true;
                yearSelect.appendChild(option);
            });
            categorySelect.addEventListener('change', () => {
                config.selectedCategory = categorySelect.value;
                console.log(`Selected category: ${config.selectedCategory}`);
                if (geojsonData) updateChoropleth();
            });

            yearSelect.addEventListener('change', () => {
                config.selectedYear = yearSelect.value;
                console.log(`Selected year: ${config.selectedYear}`);
                if (geojsonData) updateChoropleth();
            });
        });

        // Listen for Livewire event
        document.addEventListener('livewire:init', function() {
            Livewire.on('loadGeoJSON', (event) => {
                geojsonData = event.geojsonData;
                initializeMap(geojsonData);
            });
        });

        function getValue(feature, indicator, year) {
            const props = feature.properties;
            if (props[indicator] && typeof props[indicator] === 'object') {
                return props[indicator][year];
            }
            return null;
        }

        function getAllValues(indicator, year) {
            const values = [];
            if (!geojsonData || !geojsonData.features) return values;

            geojsonData.features.forEach(feature => {
                const val = getValue(feature, indicator, year);
                if (val !== null && val !== undefined && val !== 0) {
                    values.push(val);
                }
            });
            return values;
        }

        function getColor(value, indicator, minVal, maxVal) {
            if (value === null || value === undefined || value === 0) return '#cccccc';

            // Normalize between 0-1
            const t = (value - minVal) / (maxVal - minVal);

            // Color scales based on indicator
            if (indicator === 'IPM') {
                return chroma.scale(['#fee5d9', '#fcbba1', '#fc9272', '#fb6a4a', '#de2d26', '#a50f15'])
                    .domain([minVal, maxVal])(value).hex();
            } else if (indicator === 'Umur Harapan Hidup (Tahun)') {
                return chroma.scale(['#edf8fb', '#b3cde3', '#8c96c6', '#88419d'])
                    .domain([minVal, maxVal])(value).hex();
            } else if (indicator === 'Pengeluaran (Ribu rupiah/orang/tahun)') {
                return chroma.scale(['#edf8e9', '#bae4b3', '#74c476', '#238b45'])
                    .domain([minVal, maxVal])(value).hex();
            } else {
                return chroma.scale(['#f7fbff', '#deebf7', '#9ecae1', '#3182bd'])
                    .domain([minVal, maxVal])(value).hex();
            }
        }

        function updateLegend(indicator, year, values) {
            if (!values || values.length === 0) {
                document.getElementById('legend-content').innerHTML = '<div>No data available</div>';
                return;
            }

            const minVal = Math.min(...values);
            const maxVal = Math.max(...values);
            const steps = 5;
            const stepSize = (maxVal - minVal) / steps;

            let indicatorLabel = config.categories[indicator] || indicator;
            let html = `<div style="margin-bottom: 8px;"><strong style="color: #1a1a1a; font-size: 13px;">${indicatorLabel} - ${year}</strong></div>`;
            html += `<div style="font-size: 11px; color: #444; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Min: ${minVal.toFixed(2)} | Max: ${maxVal.toFixed(2)}</div>`;

            for (let i = 0; i <= steps; i++) {
                const value = minVal + (stepSize * i);
                const color = getColor(value, indicator, minVal, maxVal);


                let rangeText = '';
                if (i === 0) {
                    rangeText = `≤ ${value.toFixed(2)}`;
                } else if (i === steps) {
                    rangeText = `≥ ${value.toFixed(2)}`;
                } else {
                    const nextValue = minVal + (stepSize * (i + 1));
                    rangeText = `${value.toFixed(2)} - ${nextValue.toFixed(2)}`;
                }

                html += `
                <div style="margin-bottom: 3px; display: flex; align-items: center;">
                    <div style="width: 20px; height: 20px; background: ${color}; display: inline-block; margin-right: 8px; border-radius: 3px; border: 1px solid #ddd;"></div>
                    <span style="color: #333; font-weight: 500;">${rangeText}</span>
                </div>
                `;
            }

            document.getElementById('legend-content').innerHTML = html;
        }

        function updateChoropleth() {
            const indicator = config.selectedCategory;
            const year = config.selectedYear;

            if (!currentLayer) return;

            // Get values for color scaling
            const values = getAllValues(indicator, year);
            const minVal = values.length > 0 ? Math.min(...values) : 0;
            const maxVal = values.length > 0 ? Math.max(...values) : 100;


            updateLegend(indicator, year, values);

            // Update layer styles
            currentLayer.eachLayer(function(layer) {
                const value = getValue(layer.feature, indicator, year);
                let color = '#cccccc';
                if (value !== null && value !== undefined && value !== 0) {
                    color = getColor(value, indicator, minVal, maxVal);
                }
                layer.setStyle({
                    fillColor: color,
                    fillOpacity: 0.7,
                    color: '#ffffff',
                    weight: 1,
                    opacity: 1
                });
            });
        }

        function initializeMap(geojsonData) {
            // Check if map already exists and remove it
            if (map) {
                map.remove();
            }

            // Initialize map
            map = L.map('map').setView([-0.5, 100.5], 8);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; CartoDB',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);
            console.log('GeoJSON data loaded:', geojsonData);
            // Add GeoJSON layer with choropleth styling
            currentLayer = L.geoJSON(geojsonData, {
                style: function(feature) {
                    const indicator = config.selectedCategory;
                    const year = config.selectedYear;
                    const value = getValue(feature, indicator, year);

                    // Default color
                    let color = '#cccccc';

                    if (value !== null && value !== undefined && value !== 0) {
                        const values = getAllValues(indicator, year);
                        const minVal = values.length > 0 ? Math.min(...values) : 0;
                        const maxVal = values.length > 0 ? Math.max(...values) : 100;
                        color = getColor(value, indicator, minVal, maxVal);
                    }

                    return {
                        fillColor: color,
                        weight: 1,
                        opacity: 1,
                        color: '#ffffff',
                        fillOpacity: 0.7,
                        dashArray: null
                    };
                },
                onEachFeature: function(feature, layer) {
                    const props = feature.properties;
                    const regencyName = props.NAME_2_fixed || props.NAME_2 || 'Unknown';

                    // Hover effect
                    layer.on('mouseover', function(e) {
                        this.setStyle({
                            weight: 2,
                            color: '#333',
                            fillOpacity: 0.85
                        });

                        const indicator = config.selectedCategory;
                        const year = config.selectedYear;
                        const value = getValue(feature, indicator, year);
                        const indicatorLabel = config.categories[indicator] || indicator;

                        const hoverDiv = document.getElementById('hover-info');
                        if (hoverDiv) {
                            hoverDiv.innerHTML = `<strong>${regencyName}</strong><br>${indicatorLabel} ${year}: ${value !== null && value !== 0 ? value.toFixed(2) : 'No Data'}`;
                        }
                    });

                    layer.on('mouseout', function(e) {
                        currentLayer.resetStyle(this);
                        const hoverDiv = document.getElementById('hover-info');
                        if (hoverDiv) {
                            hoverDiv.innerHTML = 'Hover over regions for details';
                        }
                    });

                    // Popup content
                    layer.bindPopup(function(layer) {
                        const year = config.selectedYear;
                        let popupContent = `
                            <div style="min-width: 250px;">
                                <h4 style="margin: 0 0 8px 0;">${regencyName}</h4>
                                <hr style="margin: 5px 0;">
                                <table style="width: 100%; font-size: 12px;">
                        `;

                        // Add all indicators for selected year
                        Object.keys(config.categories).forEach(ind => {
                            const val = getValue(feature, ind, year);
                            if (val !== null && val !== 0) {
                                popupContent += `
                                    <tr>
                                        <td><strong>${config.categories[ind]}:</strong></td>
                                        <td style="text-align: right;">${val.toFixed(2)}</td>
                                    </tr>
                                `;
                            }
                        });

                        popupContent += `</table></div>`;
                        return popupContent;
                    });
                }
            }).addTo(map);

            // Update legend
            const values = getAllValues(config.selectedCategory, config.selectedYear);
            updateLegend(config.selectedCategory, config.selectedYear, values);
            try {
                map.fitBounds(currentLayer.getBounds());
            } catch(e) {
                console.warn('Could not fit bounds:', e);
                map.setView([-0.5, 100.5], 8);
            }
        }
    </script>
</div>
