<?php

namespace Tests\Unit;

use App\Experto\BaseConocimiento;
use App\Experto\MotorCompatibilidad;
use PHPUnit\Framework\TestCase;

class MotorCompatibilidadTest extends TestCase
{
    private function motor(): MotorCompatibilidad
    {
        return new MotorCompatibilidad(new BaseConocimiento);
    }

    public function test_coincidencia_total_en_el_perfil(): void
    {
        $resultado = $this->motor()->inferir(
            ['PHP', 'MySQL', 'JavaScript'],
            ['PHP', 'MySQL', 'JavaScript'],
            [],
            null,
            null,
            null,
            null,
        );

        $this->assertTrue($resultado->evaluable);
        $this->assertSame(100, $resultado->porcentaje);
        $this->assertSame([], $resultado->faltantes);
    }

    public function test_js_es_sinonimo_de_javascript_y_java_no(): void
    {
        $motor = $this->motor();

        $sinonimo = $motor->inferir(['JavaScript'], ['JS'], [], null, null, null, null);
        $otro = $motor->inferir(['JavaScript'], ['Java'], [], null, null, null, null);

        $this->assertSame(100, $sinonimo->porcentaje);
        $this->assertSame(0, $otro->porcentaje);
        $this->assertSame(['JavaScript'], $otro->faltantes);
    }

    public function test_laravel_en_el_perfil_aporta_php_en_parte(): void
    {
        $resultado = $this->motor()->inferir(
            ['PHP', 'MySQL', 'JavaScript'],
            ['Laravel'],
            [],
            null,
            null,
            null,
            null,
        );

        $this->assertSame(22, $resultado->porcentaje);
        $this->assertSame(['MySQL', 'JavaScript'], $resultado->faltantes);
        $this->assertStringContainsString('Laravel en el perfil aporta PHP.', $resultado->reglas[0]);
    }

    public function test_la_experiencia_pesa_menos_que_el_perfil(): void
    {
        $resultado = $this->motor()->inferir(
            ['PHP'],
            [],
            [['desde' => null, 'hasta' => null, 'habilidades' => ['PHP']]],
            null,
            null,
            null,
            null,
        );

        $this->assertSame(67, $resultado->porcentaje);
        $this->assertStringContainsString('experiencia laboral', $resultado->reglas[0]);
    }

    public function test_el_cv_menciona_la_habilidad_con_peso_bajo(): void
    {
        $resultado = $this->motor()->inferir(
            ['MySQL'],
            [],
            [],
            null,
            null,
            null,
            null,
            'Trabajé con MySQL durante dos años.',
        );

        $this->assertSame(33, $resultado->porcentaje);
        $this->assertStringContainsString('El CV menciona MySQL.', $resultado->reglas[0]);
    }

    public function test_java_escrito_en_el_cv_no_cuenta_como_javascript(): void
    {
        $resultado = $this->motor()->inferir(
            ['JavaScript'],
            [],
            [],
            null,
            null,
            null,
            null,
            'Desarrollador Java con cinco años.',
        );

        $this->assertSame(0, $resultado->porcentaje);
    }

    public function test_sin_habilidades_en_la_solicitud_no_se_evalua(): void
    {
        $resultado = $this->motor()->inferir([], ['PHP'], [], 3, 2, 1, 1);

        $this->assertFalse($resultado->evaluable);
        $this->assertNull($resultado->porcentaje);
    }

    public function test_los_trabajos_superpuestos_no_suman_dos_veces(): void
    {
        $resultado = $this->motor()->inferir(
            ['PHP'],
            ['PHP'],
            [
                ['desde' => '2020-01-01', 'hasta' => '2022-01-01', 'habilidades' => []],
                ['desde' => '2021-01-01', 'hasta' => '2023-01-01', 'habilidades' => []],
            ],
            4,
            null,
            null,
            null,
            '',
            null,
            '2026-01-01',
        );

        $this->assertSame(96, $resultado->porcentaje);
        $this->assertStringContainsString('3,0 años', implode(' ', $resultado->reglas));
    }

    public function test_la_modalidad_remota_no_resta_por_la_ciudad(): void
    {
        $resultado = $this->motor()->inferir(
            ['PHP'],
            [],
            [],
            null,
            2,
            10,
            20,
        );

        $this->assertSame(6, $resultado->porcentaje);
        $this->assertStringContainsString('remota', implode(' ', $resultado->reglas));
    }
}
