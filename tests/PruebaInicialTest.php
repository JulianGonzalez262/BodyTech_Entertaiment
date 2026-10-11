<?php

use PHPUnit\Framework\TestCase;

class PruebaInicialTest extends TestCase
{
    public function testSumaDosNumeros()
    {
        $resultado = 2 + 3;

        $this->assertSame(5, $resultado);
    }
}