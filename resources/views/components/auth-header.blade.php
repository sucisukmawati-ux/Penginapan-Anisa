@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-2 text-center">
    <flux:heading size="xl" level="1" class="!text-[#342d1e]">{{ $title }}</flux:heading>
    <flux:subheading class="!text-[#817b6e]">{{ $description }}</flux:subheading>
</div>
