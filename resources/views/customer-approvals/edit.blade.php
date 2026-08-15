@extends('layouts.app')
@section('title', 'Freigabeanfrage bearbeiten')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('customer-approvals.update', $approval) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kunde <span class="text-red-500">*</span></label>
                <select name="customer_id" id="customer_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', $approval->customer_id) == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}{{ ! $customer->email ? ' (keine E-Mail hinterlegt)' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Projekt <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <select name="project_id" id="project_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">– Kein Projekt –</option>
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}" data-customer="{{ $project->customer_id }}"
                            {{ old('project_id', $approval->project_id) == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titel <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $approval->title) }}" required maxlength="255"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung <span class="text-red-500">*</span></label>
                <textarea name="description" rows="6" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $approval->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Gültig bis <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <input type="date" name="expires_at"
                       value="{{ old('expires_at', $approval->expires_at?->toDateString()) }}"
                       class="w-full sm:w-56 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">Leer lassen für unbegrenzte Gültigkeit.</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg text-sm">
                Speichern
            </button>
            <a href="{{ route('customer-approvals.show', $approval) }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>

<script>
    (function () {
        var customerSelect = document.getElementById('customer_id');
        var projectSelect  = document.getElementById('project_id');
        var projectOptions = Array.prototype.slice.call(projectSelect.options, 1);

        function filterProjects() {
            var customerId   = customerSelect.value;
            var currentValue = projectSelect.value;
            var stillValid   = false;

            projectOptions.forEach(function (opt) {
                var matches = !customerId || opt.dataset.customer === customerId;
                opt.hidden   = !matches;
                opt.disabled = !matches;
                if (matches && opt.value === currentValue) stillValid = true;
            });

            if (!stillValid) projectSelect.value = '';
        }

        customerSelect.addEventListener('change', filterProjects);
        filterProjects();
    })();
</script>
@endsection
