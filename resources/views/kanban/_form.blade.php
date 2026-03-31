<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Titel <span class="text-red-500">*</span></label>
    <input type="text" name="title" value="{{ old('title') }}" required autofocus
           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung</label>
    <textarea name="description" rows="3" placeholder="Optional..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Projekt <span class="text-red-500">*</span></label>
        <select name="project_id" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">– wählen –</option>
            @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>
                {{ $p->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Priorität</label>
        <select name="priority"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="low"    {{ old('priority') === 'low'    ? 'selected' : '' }}>Niedrig</option>
            <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Mittel</option>
            <option value="high"   {{ old('priority') === 'high'   ? 'selected' : '' }}>Hoch</option>
        </select>
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Zugewiesen an</label>
        <select name="assigned_to"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">– niemanden –</option>
            @foreach($members as $m)
            <option value="{{ $m->id }}" {{ old('assigned_to') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fälligkeitsdatum</label>
        <input type="date" name="due_date" value="{{ old('due_date') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
</div>
