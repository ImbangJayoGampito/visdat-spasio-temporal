<?php

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;
use App\Helpers\Page;
new class extends Component {
    use Toast;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    public function viewPage($id): void
    {
        $selectedPage = $this->pages()->firstWhere('id', $id);
        if ($selectedPage) {
            $this->redirect(route($selectedPage['route'], $selectedPage));
        }
    }
    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    // Delete action
    public function delete($id): void
    {
        $this->warning("Will delete #$id", 'It is fake.', position: 'toast-bottom');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'title', 'label' => 'Title', 'class' => 'w-64'],
            ['key' => 'description', 'label' => 'Description', 'class' => 'w-64'],
            ['key' => 'author', 'label' => 'Author', 'class' => 'w-20'],
        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
    public function pages(): Collection
    {
    $pages = [
           new Page(
               title: "Tutorial Leaflet",
               description: "Pengaturan dasar leaflet yang membahas tentang cara pemasangan dan pengaturan tampilan vektor seperti titik, garis, dan bahkan poligon",
               author: "Imbang",
               route: "map.leaflet-basics",
               category: "tutorial",
           ),

       ];
        return collect($pages)->map(fn(Page $page) => [

            'title' => $page->title,
            'description' => $page->description,
            'author' => $page->author,
            'route' => $page->route,
            'category' => $page->category,
        ])->values()->map(function (array $item, int $index) {
                $item['id'] = $index + 1;
                return $item;
            })->sortBy([[...array_values($this->sortBy)]])
            ->when($this->search, function (Collection $collection) {
                $search = strtolower($this->search);
                return $collection->filter(fn(array $item) => str($item['title'])->contains($search, true)
                || str($item['description'])->contains($search, true)
                || str($item['author'])->contains($search, true)
                || str($item['category'])->contains($search, true));


            });
    }

    public function with(): array
    {
        return [
            'pages' => $this->pages(),
            'headers' => $this->headers()
        ];
    }
};

?>

<div>
    <!-- HEADER -->
    <x-header title="Hello" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
        </x-slot:actions>
    </x-header>

    <!-- TABEL BLOG  -->
    <x-card shadow class="bg-base-200/50">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">

            @foreach($pages as $page)
                <x-card
                    shadow
                    class="hover:shadow-xl transition-shadow duration-300 bg-base-100"
                    title="{{ $page['title'] }}"
                    subtitle="Oleh {{ $page['author'] }}"
                >
                    <div class="mb-3">
                        <span class="badge badge-primary badge-sm">{{ $page['category'] ?? 'Uncategorized' }}</span>
                    </div>
                    <div class="text-sm text-base-content/70 line-clamp-3 mb-4">
                        {{ $page['description'] }}
                    </div>

                    <x-slot:actions class="justify-end">

                        <x-button
                            icon="o-eye"
                            label="Tampilkan"
                            class="btn-sm btn-ghost"
                            wire:click="viewPage({{ $page['id'] }})"
                            spinner
                        />
                    </x-slot:actions>
                </x-card>
            @endforeach

        </div>

        <!-- If empty state -->
        @empty($pages)
            <div class="text-center py-12 text-base-content/60">
                <x-icon name="o-book-open" class="w-16 h-16 mx-auto mb-4 opacity-50" />
                <p>Tidak ada halaman yang ditemukan.</p>
            </div>
        @endempty

    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" @keydown.enter="$wire.drawer = false" />

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
