<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Wird geworfen wenn eine "Warten bis"-Aktion die Bedingung nicht erfüllt findet.
 * Trägt alle Informationen die benötigt werden, um den Zustand zu speichern.
 */
class AutomationWaitingException extends RuntimeException
{
    public function __construct(
        /** Schritte die nach dem Wait noch ausgeführt werden sollen */
        public readonly array $remainingSteps,

        /** Bereits aufgelöste Bedingungsparameter */
        public readonly string $conditionModel,
        public readonly string $conditionId,
        public readonly string $conditionField,
        public readonly string $conditionOperator,
        public readonly string $conditionValue,
        public readonly int    $checkIntervalMinutes,
        public readonly int    $timeoutMinutes,
    ) {
        parent::__construct("wait_until: Bedingung noch nicht erfüllt – pausiere Automation.");
    }
}
