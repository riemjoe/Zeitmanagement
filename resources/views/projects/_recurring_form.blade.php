{{--
    Wiederverwendbares Formular für wiederkehrende Aufgaben.
    Erwartet: $members (Collection<User>)
    Optional: $editing (bool) – wenn true, werden Alpine editData-Bindings genutzt
--}}
@php $e = $editing ?? false; @endphp

{{-- Titel --}}
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Titel <span class="text-red-500">*</span></label>
    @if($e)
        <input type="text" name="title" x-model="editData.title" required
               class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    @else
        <input type="text" name="title" value="{{ old('title') }}" required autofocus
               class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    @endif
</div>

{{-- Beschreibung --}}
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Beschreibung</label>
    @if($e)
        <textarea name="description" x-model="editData.description" rows="2"
                  class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400"></textarea>
    @else
        <textarea name="description" rows="2"
                  class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">{{ old('description') }}</textarea>
    @endif
</div>

{{-- Rhythmus --}}
<div class="grid grid-cols-2 gap-3"
     x-data="{ freq: {{ $e ? 'editData.frequency' : "'weekly'" }} }"
     @change.capture="if ($event.target.name === 'frequency') freq = $event.target.value">

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Wiederholung <span class="text-red-500">*</span></label>
        @if($e)
            <select name="frequency" x-model="editData.frequency"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
        @else
            <select name="frequency"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
        @endif
                <option value="daily">Täglich</option>
                <option value="weekly" {{ !$e && old('frequency','weekly')==='weekly' ? 'selected':'' }}>Wöchentlich</option>
                <option value="monthly">Monatlich</option>
            </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Alle … (Intervall)</label>
        @if($e)
            <input type="number" name="frequency_interval" x-model="editData.frequency_interval"
                   min="1" max="52"
                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
        @else
            <input type="number" name="frequency_interval" value="{{ old('frequency_interval', 1) }}"
                   min="1" max="52"
                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
        @endif
    </div>

    {{-- Wochentag (nur bei weekly) --}}
    <div x-show="freq === 'weekly'" x-cloak class="col-span-2">
        <label class="block text-xs font-medium text-gray-600 mb-1">Wochentag</label>
        @if($e)
            <select name="day_of_week" x-model="editData.day_of_week"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
        @else
            <select name="day_of_week"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
        @endif
                <option value="1" {{ !$e && old('day_of_week',1)==1 ? 'selected':'' }}>Montag</option>
                <option value="2">Dienstag</option>
                <option value="3">Mittwoch</option>
                <option value="4">Donnerstag</option>
                <option value="5">Freitag</option>
                <option value="6">Samstag</option>
                <option value="0">Sonntag</option>
            </select>
    </div>

    {{-- Tag des Monats (nur bei monthly) --}}
    <div x-show="freq === 'monthly'" x-cloak class="col-span-2">
        <label class="block text-xs font-medium text-gray-600 mb-1">Tag des Monats (1–28)</label>
        @if($e)
            <input type="number" name="day_of_month" x-model="editData.day_of_month"
                   min="1" max="28" placeholder="z.B. 1"
                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
        @else
            <input type="number" name="day_of_month" value="{{ old('day_of_month', 1) }}"
                   min="1" max="28" placeholder="z.B. 1"
                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
        @endif
    </div>
</div>

{{-- Uhrzeit --}}
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Uhrzeit der Erstellung</label>
    @if($e)
        <input type="time" name="time_of_day" x-model="editData.time_of_day"
               class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
    @else
        <input type="time" name="time_of_day" value="{{ old('time_of_day', '06:00') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
    @endif
    <p class="text-xs text-gray-400 mt-1">Der Task wird an diesem Tag zur angegebenen Uhrzeit automatisch erstellt.</p>
</div>

{{-- Priorität · Kanban-Status · Fälligkeit --}}
<div class="grid grid-cols-3 gap-3">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Priorität</label>
        @if($e)
            <select name="priority" x-model="editData.priority"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
        @else
            <select name="priority"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
        @endif
                <option value="low">Niedrig</option>
                <option value="medium" {{ !$e && old('priority','medium')==='medium' ? 'selected':'' }}>Mittel</option>
                <option value="high">Hoch</option>
            </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Startstatus</label>
        @if($e)
            <select name="kanban_status" x-model="editData.kanban_status"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
        @else
            <select name="kanban_status"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
        @endif
                <option value="ready" {{ !$e && old('kanban_status','ready')==='ready' ? 'selected':'' }}>Ready</option>
                <option value="wip">In Arbeit</option>
                <option value="testing">Testing</option>
                <option value="completed">Abgeschlossen</option>
            </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Fälligkeit (Tage)</label>
        @if($e)
            <input type="number" name="due_days_offset" x-model="editData.due_days_offset"
                   min="0" max="365" placeholder="0 = keine"
                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
        @else
            <input type="number" name="due_days_offset" value="{{ old('due_days_offset', 0) }}"
                   min="0" max="365" placeholder="0 = keine"
                   class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
        @endif
    </div>
</div>

{{-- Zuweisung --}}
@if($members->isNotEmpty())
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Zuweisen an</label>
    @if($e)
        <select name="assigned_to" x-model="editData.assigned_to"
                class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
    @else
        <select name="assigned_to"
                class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
    @endif
            <option value="">– niemanden –</option>
            @foreach($members as $m)
            <option value="{{ $m->id }}">{{ $m->name }}</option>
            @endforeach
        </select>
</div>
@endif

{{-- Wartungsaufgabe --}}
<div class="flex items-center gap-2 pt-1">
    <input type="hidden" name="is_maintenance" value="0">
    @if($e)
        <input type="checkbox" name="is_maintenance" id="is_maintenance_edit" value="1"
               :checked="editData.is_maintenance"
               @change="editData.is_maintenance = $event.target.checked"
               class="w-4 h-4 accent-amber-500 rounded">
    @else
        <input type="checkbox" name="is_maintenance" id="is_maintenance_new" value="1"
               {{ old('is_maintenance') ? 'checked' : '' }}
               class="w-4 h-4 accent-amber-500 rounded">
    @endif
    <label for="{{ $e ? 'is_maintenance_edit' : 'is_maintenance_new' }}"
           class="text-xs font-medium text-gray-700 flex items-center gap-1.5">
        <i class="ph-bold ph-wrench text-amber-500"></i>
        Als Wartungsaufgabe markieren
        <span class="text-gray-400 font-normal">(erscheint im Wartungsplan-Kalender)</span>
    </label>
</div>
