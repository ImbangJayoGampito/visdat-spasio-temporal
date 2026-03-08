<?php

Route::livewire("/", "pages::users.index")->name("home");
Route::livewire("/map/leaflet-basics", "pages::map.leaflet-basics")->name(
    "map.leaflet-basics",
);
