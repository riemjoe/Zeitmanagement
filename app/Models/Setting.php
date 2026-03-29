<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Alle Einstellungen als Key-Value-Array zurückgeben.
     */
    public static function getAll(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Einen einzelnen Wert abrufen.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Einen Wert setzen oder aktualisieren.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
