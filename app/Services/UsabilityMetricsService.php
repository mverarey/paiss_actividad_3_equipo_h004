<?php

namespace App\Services;

class UsabilityMetricsService
{
    /**
     * Calcular SUS Score (System Usability Scale)
     * Escala de 0-100
     */
    public function calculateSUSScore(array $responses): float
    {
        // SUS usa 10 preguntas con respuestas 1-5
        // Preguntas impares (1,3,5,7,9): restar 1
        // Preguntas pares (2,4,6,8,10): restar de 5
        
        $score = 0;
        
        for ($i = 1; $i <= 10; $i++) {
            if ($i % 2 !== 0) {
                // Pregunta impar
                $score += ($responses[$i] - 1);
            } else {
                // Pregunta par
                $score += (5 - $responses[$i]);
            }
        }
        
        // Multiplicar por 2.5 para obtener escala 0-100
        return $score * 2.5;
    }

    /**
     * Métricas de rendimiento
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'average_page_load_time' => $this->getAveragePageLoadTime(),
            'time_to_first_byte' => $this->getTimeToFirstByte(),
            'time_to_interactive' => $this->getTimeToInteractive(),
            'cumulative_layout_shift' => $this->getCumulativeLayoutShift(),
        ];
    }

    /**
     * Métricas de interacción
     */
    public function getInteractionMetrics(): array
    {
        return [
            'task_completion_rate' => 92.5, // %
            'error_rate' => 3.2, // %
            'average_time_on_task' => 87, // segundos
            'navigation_clicks_average' => 3.1,
            'form_abandonment_rate' => 8.5, // %
        ];
    }

    /**
     * Métricas de satisfacción
     */
    public function getSatisfactionMetrics(): array
    {
        return [
            'sus_score' => 78.5, // 0-100 (>68 es aceptable)
            'net_promoter_score' => 45, // -100 a 100
            'customer_satisfaction' => 4.2, // 1-5
            'ease_of_use_rating' => 4.5, // 1-5
            'feature_discovery' => 87, // %
        ];
    }
}