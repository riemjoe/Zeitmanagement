<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Fragebögen (Vorlagen) ─────────────────────────────────────────
        Schema::create('survey_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            // Schwellenwerte für die Gesamtauswertung (0–100)
            $table->unsignedTinyInteger('good_threshold')->default(70); // >= gut
            $table->unsignedTinyInteger('bad_threshold')->default(40);  // <= schlecht
            $table->timestamps();
        });

        // ── 2. Sektionen innerhalb eines Fragebogens ─────────────────────────
        Schema::create('survey_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_template_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        // ── 3. Fragen ────────────────────────────────────────────────────────
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_template_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable(); // Hinweistext / Erklärung
            // Typen: range, number, text, select
            $table->enum('type', ['range', 'number', 'text', 'select']);
            $table->boolean('is_required')->default(true);
            // Gewichtung für die Gesamtbewertung (1 = normal, 5 = sehr wichtig)
            $table->unsignedTinyInteger('weight')->default(1);
            $table->unsignedSmallInteger('position')->default(0);
            // JSON mit typspezifischen Einstellungen:
            // range/number: { min, max, step, good_from, bad_to }
            // select: (Optionen sind in survey_options)
            // text: { placeholder }
            $table->text('settings')->nullable();
            $table->timestamps();
        });

        // ── 4. Antwortoptionen (für Select-Fragen) ───────────────────────────
        Schema::create('survey_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            // Normierter Score dieser Option: 0 (schlecht) bis 100 (sehr gut)
            $table->unsignedTinyInteger('score')->default(50);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        // ── 5. Umfrage-Instanzen (einem Kunden zugewiesen) ───────────────────
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_template_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); // kann vom Template-Titel abweichen
            $table->string('token', 64)->unique(); // öffentlicher URL-Token
            // null = unbegrenzt
            $table->unsignedSmallInteger('max_responses')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 6. Eingereichte Antworten (eine pro Ausfüllung) ──────────────────
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('respondent_name')->nullable();
            $table->string('respondent_email')->nullable();
            // Berechneter Gesamtscore 0–100
            $table->decimal('total_score', 5, 2)->nullable();
            // Auswertung: good | neutral | bad | null (wenn keine bewerteten Fragen)
            $table->enum('verdict', ['good', 'neutral', 'bad'])->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();
        });

        // ── 7. Einzelne Antworten pro Frage ─────────────────────────────────
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            // Rohwert – je nach Typ befüllt
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 10, 2)->nullable();
            // Normierter Score dieser Antwort: 0–100 (null bei Text-Fragen)
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('survey_options');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('survey_sections');
        Schema::dropIfExists('survey_templates');
    }
};
