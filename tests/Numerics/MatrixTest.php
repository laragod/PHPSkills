<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\Numerics;

use InvalidArgumentException;
use Laragod\Skills\Numerics\DiagonalMatrix;
use Laragod\Skills\Numerics\IdentityMatrix;
use Laragod\Skills\Numerics\Matrix;
use Laragod\Skills\Numerics\SquareMatrix;
use Laragod\Skills\Numerics\Vector;
use Laragod\Skills\Tests\TestCase;
use LogicException;

final class MatrixTest extends TestCase
{
    private const ERROR_TOLERANCE = 0.0000001;

    public function test_two_by_two_determinant(): void
    {
        $a = new SquareMatrix(1, 2,
            3, 4);

        $this->assertEqualsWithDelta(-2, $a->getDeterminant(), self::ERROR_TOLERANCE);

        $b = new SquareMatrix(3, 4,
            5, 6);

        $this->assertEqualsWithDelta(-2, $b->getDeterminant(), self::ERROR_TOLERANCE);

        $c = new SquareMatrix(1, 1,
            1, 1);

        $this->assertEqualsWithDelta(0, $c->getDeterminant(), self::ERROR_TOLERANCE);

        $d = new SquareMatrix(12, 15,
            17, 21);

        $this->assertEqualsWithDelta(12 * 21 - 15 * 17, $d->getDeterminant(), self::ERROR_TOLERANCE);
    }

    public function test_three_by_three_determinant(): void
    {
        $a = new SquareMatrix(1, 2, 3,
            4, 5, 6,
            7, 8, 9);

        $this->assertEqualsWithDelta(0, $a->getDeterminant(), self::ERROR_TOLERANCE);

        $pi = new SquareMatrix(3, 1, 4,
            1, 5, 9,
            2, 6, 5);

        // Verified against http://www.wolframalpha.com/input/?i=determinant+%7B%7B3%2C1%2C4%7D%2C%7B1%2C5%2C9%7D%2C%7B2%2C6%2C5%7D%7D
        $this->assertEqualsWithDelta(-90, $pi->getDeterminant(), self::ERROR_TOLERANCE);
    }

    public function test_four_by_four_determinant(): void
    {
        $a = new SquareMatrix(1, 2, 3, 4,
            5, 6, 7, 8,
            9, 10, 11, 12,
            13, 14, 15, 16);

        $this->assertEqualsWithDelta(0, $a->getDeterminant(), self::ERROR_TOLERANCE);

        $pi = new SquareMatrix(3, 1, 4, 1,
            5, 9, 2, 6,
            5, 3, 5, 8,
            9, 7, 9, 3);

        // Verified against http://www.wolframalpha.com/input/?i=determinant+%7B+%7B3%2C1%2C4%2C1%7D%2C+%7B5%2C9%2C2%2C6%7D%2C+%7B5%2C3%2C5%2C8%7D%2C+%7B9%2C7%2C9%2C3%7D%7D
        $this->assertEqualsWithDelta(98, $pi->getDeterminant(), self::ERROR_TOLERANCE);
    }

    public function test_eight_by_eight_determinant(): void
    {
        $a = new SquareMatrix(1, 2, 3, 4, 5, 6, 7, 8,
            9, 10, 11, 12, 13, 14, 15, 16,
            17, 18, 19, 20, 21, 22, 23, 24,
            25, 26, 27, 28, 29, 30, 31, 32,
            33, 34, 35, 36, 37, 38, 39, 40,
            41, 42, 32, 44, 45, 46, 47, 48,
            49, 50, 51, 52, 53, 54, 55, 56,
            57, 58, 59, 60, 61, 62, 63, 64);

        $this->assertEqualsWithDelta(0, $a->getDeterminant(), self::ERROR_TOLERANCE);

        $pi = new SquareMatrix(3, 1, 4, 1, 5, 9, 2, 6,
            5, 3, 5, 8, 9, 7, 9, 3,
            2, 3, 8, 4, 6, 2, 6, 4,
            3, 3, 8, 3, 2, 7, 9, 5,
            0, 2, 8, 8, 4, 1, 9, 7,
            1, 6, 9, 3, 9, 9, 3, 7,
            5, 1, 0, 5, 8, 2, 0, 9,
            7, 4, 9, 4, 4, 5, 9, 2);

        // Verified against http://www.wolframalpha.com/input/?i=det+%7B%7B3%2C1%2C4%2C1%2C5%2C9%2C2%2C6%7D%2C%7B5%2C3%2C5%2C8%2C9%2C7%2C9%2C3%7D%2C%7B2%2C3%2C8%2C4%2C6%2C2%2C6%2C4%7D%2C%7B3%2C3%2C8%2C3%2C2%2C7%2C9%2C5%7D%2C%7B0%2C2%2C8%2C8%2C4%2C1%2C9%2C7%7D%2C%7B1%2C6%2C9%2C3%2C9%2C9%2C3%2C7%7D%2C%7B5%2C1%2C0%2C5%2C8%2C2%2C0%2C9%7D%2C%7B7%2C4%2C9%2C4%2C4%2C5%2C9%2C2%7D%7D
        $this->assertEqualsWithDelta(1378143, $pi->getDeterminant(), self::ERROR_TOLERANCE);
    }

    public function test_equals(): void
    {
        $a = new SquareMatrix(1, 2,
            3, 4);

        $b = new SquareMatrix(1, 2,
            3, 4);

        $this->assertTrue($a->equals($b));

        $c = Matrix::fromRowsColumns(2, 3,
            1, 2, 3,
            4, 5, 6);

        $d = Matrix::fromRowsColumns(2, 3,
            1, 2, 3,
            4, 5, 6);

        $this->assertTrue($c->equals($d));

        $e = Matrix::fromRowsColumns(3, 2,
            1, 4,
            2, 5,
            3, 6);

        $f = $e->getTranspose();
        $this->assertTrue($d->equals($f));

        // Different sizes are never equal
        $this->assertFalse($a->equals($c));

        // Test rounding (thanks to nsp on GitHub for finding this case)
        $g = new SquareMatrix(1, 2.00000000000001,
            3, 4);

        $h = new SquareMatrix(1, 2,
            3, 4);

        $this->assertTrue($g->equals($h));
    }

    public function test_adjugate(): void
    {
        // From Wikipedia: http://en.wikipedia.org/wiki/Adjugate_matrix
        $a = new SquareMatrix(1, 2,
            3, 4);

        $b = new SquareMatrix(4, -2,
            -3, 1);

        $this->assertTrue($b->equals($a->getAdjugate()));

        $c = new SquareMatrix(-3, 2, -5,
            -1, 0, -2,
            3, -4, 1);

        $d = new SquareMatrix(-8, 18, -4,
            -5, 12, -1,
            4, -6, 2);

        $this->assertTrue($d->equals($c->getAdjugate()));
    }

    public function test_inverse(): void
    {
        // see http://www.mathwords.com/i/inverse_of_a_matrix.htm
        $a = new SquareMatrix(4, 3,
            3, 2);

        $b = new SquareMatrix(-2, 3,
            3, -4);

        $aInverse = $a->getInverse();
        $this->assertTrue($b->equals($aInverse));

        $identity2x2 = new IdentityMatrix(2);

        $aaInverse = Matrix::multiply($a, $aInverse);
        $this->assertTrue($identity2x2->equals($aaInverse));

        $c = new SquareMatrix(1, 2, 3,
            0, 4, 5,
            1, 0, 6);

        $cInverse = $c->getInverse();
        $d = Matrix::scalarMultiply((1.0 / 22), new SquareMatrix(24, -12, -2,
            5, 3, -5,
            -4, 2, 4));

        $this->assertTrue($d->equals($cInverse));
        $identity3x3 = new IdentityMatrix(3);

        $ccInverse = Matrix::multiply($c, $cInverse);
        $this->assertTrue($identity3x3->equals($ccInverse));

        $oneByOne = new SquareMatrix(4);
        $this->assertTrue((new SquareMatrix(0.25))->equals($oneByOne->getInverse()));
    }

    public function test_vector_and_diagonal_matrix(): void
    {
        $vector = new Vector([1, 2, 3]);

        $this->assertSame(3, $vector->getRowCount());
        $this->assertSame(1, $vector->getColumnCount());
        $this->assertSame(2.0, $vector->getValue(1, 0));

        $diagonal = new DiagonalMatrix([1, 2]);

        $this->assertTrue($diagonal->equals(new SquareMatrix(1, 0, 0, 2)));
        $this->assertTrue(Matrix::add($diagonal, $diagonal)->equals(new SquareMatrix(2, 0, 0, 4)));
    }

    public function test_determinant_of_non_square_matrix_is_rejected(): void
    {
        $this->expectException(LogicException::class);

        Matrix::fromRowsColumns(2, 3, 1, 2, 3, 4, 5, 6)->getDeterminant();
    }

    public function test_mismatched_sizes_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Matrix::add(new SquareMatrix(1, 2, 3, 4), new IdentityMatrix(3));
    }

    public function test_square_matrix_requires_a_square_number_of_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SquareMatrix(1, 2, 3);
    }
}
