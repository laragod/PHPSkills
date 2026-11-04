<?php

declare(strict_types=1);

namespace Laragod\Skills\Numerics;

use InvalidArgumentException;
use LogicException;

/**
 * A small, naive dense matrix. Only what TrueSkill's match quality calculation needs.
 */
class Matrix
{
    public const ERROR_TOLERANCE = 0.0000000001;

    /**
     * @param  array<int, array<int, float>>  $rowData  Values indexed by [row][column].
     */
    public function __construct(
        private readonly int $rowCount = 0,
        private readonly int $columnCount = 0,
        private array $rowData = [],
    ) {}

    /**
     * @param  array<int, array<int, float>>  $columnValues  Values indexed by [column][row].
     */
    public static function fromColumnValues(int $rows, int $columns, array $columnValues): Matrix
    {
        $result = new Matrix($rows, $columns);

        for ($currentColumn = 0; $currentColumn < $columns; $currentColumn++) {
            $currentColumnData = $columnValues[$currentColumn];

            for ($currentRow = 0; $currentRow < $rows; $currentRow++) {
                $result->setValue($currentRow, $currentColumn, $currentColumnData[$currentRow]);
            }
        }

        return $result;
    }

    /**
     * @param  float  ...$values  The values in row-major order.
     */
    public static function fromRowsColumns(int $rows, int $cols, float ...$values): Matrix
    {
        $values = array_values($values);

        if (count($values) !== $rows * $cols) {
            throw new InvalidArgumentException(sprintf('Expected %d values for a %dx%d matrix, got %d', $rows * $cols, $rows, $cols, count($values)));
        }

        $result = new Matrix($rows, $cols);
        $currentIndex = 0;

        for ($currentRow = 0; $currentRow < $rows; $currentRow++) {
            for ($currentCol = 0; $currentCol < $cols; $currentCol++) {
                $result->setValue($currentRow, $currentCol, $values[$currentIndex++]);
            }
        }

        return $result;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getColumnCount(): int
    {
        return $this->columnCount;
    }

    public function getValue(int $row, int $col): float
    {
        return $this->rowData[$row][$col];
    }

    public function setValue(int $row, int $col, float $value): void
    {
        $this->rowData[$row][$col] = $value;
    }

    public function getTranspose(): Matrix
    {
        $transposeMatrix = [];

        for ($row = 0; $row < $this->columnCount; $row++) {
            for ($column = 0; $column < $this->rowCount; $column++) {
                $transposeMatrix[$row][$column] = $this->rowData[$column][$row];
            }
        }

        return new Matrix($this->columnCount, $this->rowCount, $transposeMatrix);
    }

    private function isSquare(): bool
    {
        return $this->rowCount === $this->columnCount && $this->rowCount > 0;
    }

    public function getDeterminant(): float
    {
        if (! $this->isSquare()) {
            throw new LogicException('Matrix must be square');
        }

        if ($this->rowCount === 1) {
            return $this->rowData[0][0];
        }

        if ($this->rowCount === 2) {
            // Given:
            // | a b |
            // | c d |
            // The determinant is ad - bc
            $a = $this->rowData[0][0];
            $b = $this->rowData[0][1];
            $c = $this->rowData[1][0];
            $d = $this->rowData[1][1];

            return $a * $d - $b * $c;
        }

        // Laplace expansion along the first row. Straightforward, if slow, but the
        // matrices involved in match quality are tiny.
        // See http://en.wikipedia.org/wiki/Laplace_expansion for details
        $result = 0.0;

        for ($currentColumn = 0; $currentColumn < $this->columnCount; $currentColumn++) {
            $firstRowColValue = $this->rowData[0][$currentColumn];
            $cofactor = $this->getCofactor(0, $currentColumn);
            $result += $firstRowColValue * $cofactor;
        }

        return $result;
    }

    public function getAdjugate(): Matrix
    {
        if (! $this->isSquare()) {
            throw new LogicException('Matrix must be square');
        }

        // See http://en.wikipedia.org/wiki/Adjugate_matrix
        if ($this->rowCount === 2) {
            // Adjugate of:
            // | a b |
            // | c d |
            // is
            // | d -b |
            // | -c a |
            $a = $this->rowData[0][0];
            $b = $this->rowData[0][1];
            $c = $this->rowData[1][0];
            $d = $this->rowData[1][1];

            return new SquareMatrix(
                $d, -$b,
                -$c, $a
            );
        }

        // The adjugate is the transpose of the cofactor matrix
        $result = [];

        for ($currentColumn = 0; $currentColumn < $this->columnCount; $currentColumn++) {
            for ($currentRow = 0; $currentRow < $this->rowCount; $currentRow++) {
                $result[$currentColumn][$currentRow] = $this->getCofactor($currentRow, $currentColumn);
            }
        }

        return new Matrix($this->columnCount, $this->rowCount, $result);
    }

    public function getInverse(): Matrix
    {
        if ($this->rowCount === 1 && $this->columnCount === 1) {
            return new SquareMatrix(1.0 / $this->rowData[0][0]);
        }

        // Take the simple approach:
        // http://en.wikipedia.org/wiki/Cramer%27s_rule#Finding_inverse_matrix
        $determinantInverse = 1.0 / $this->getDeterminant();
        $adjugate = $this->getAdjugate();

        return self::scalarMultiply($determinantInverse, $adjugate);
    }

    public static function scalarMultiply(float $scalarValue, Matrix $matrix): Matrix
    {
        $rows = $matrix->getRowCount();
        $columns = $matrix->getColumnCount();
        $newValues = [];

        for ($currentRow = 0; $currentRow < $rows; $currentRow++) {
            for ($currentColumn = 0; $currentColumn < $columns; $currentColumn++) {
                $newValues[$currentRow][$currentColumn] = $scalarValue * $matrix->getValue($currentRow, $currentColumn);
            }
        }

        return new Matrix($rows, $columns, $newValues);
    }

    public static function add(Matrix $left, Matrix $right): Matrix
    {
        if ($left->getRowCount() !== $right->getRowCount() || $left->getColumnCount() !== $right->getColumnCount()) {
            throw new InvalidArgumentException('Matrices must be of the same size');
        }

        $resultMatrix = [];

        for ($currentRow = 0; $currentRow < $left->getRowCount(); $currentRow++) {
            for ($currentColumn = 0; $currentColumn < $right->getColumnCount(); $currentColumn++) {
                $resultMatrix[$currentRow][$currentColumn] =
                    $left->getValue($currentRow, $currentColumn) + $right->getValue($currentRow, $currentColumn);
            }
        }

        return new Matrix($left->getRowCount(), $right->getColumnCount(), $resultMatrix);
    }

    public static function multiply(Matrix $left, Matrix $right): Matrix
    {
        // See http://en.wikipedia.org/wiki/Matrix_multiplication for details
        if ($left->getColumnCount() !== $right->getRowCount()) {
            throw new InvalidArgumentException('The width of the left matrix must match the height of the right matrix');
        }

        $resultRows = $left->getRowCount();
        $resultColumns = $right->getColumnCount();

        $resultMatrix = [];

        for ($currentRow = 0; $currentRow < $resultRows; $currentRow++) {
            for ($currentColumn = 0; $currentColumn < $resultColumns; $currentColumn++) {
                $productValue = 0.0;

                for ($vectorIndex = 0; $vectorIndex < $left->getColumnCount(); $vectorIndex++) {
                    $productValue += $left->getValue($currentRow, $vectorIndex) * $right->getValue($vectorIndex, $currentColumn);
                }

                $resultMatrix[$currentRow][$currentColumn] = $productValue;
            }
        }

        return new Matrix($resultRows, $resultColumns, $resultMatrix);
    }

    private function getMinorMatrix(int $rowToRemove, int $columnToRemove): Matrix
    {
        // See http://en.wikipedia.org/wiki/Minor_(linear_algebra)
        $result = [];
        $actualRow = 0;

        for ($currentRow = 0; $currentRow < $this->rowCount; $currentRow++) {
            if ($currentRow === $rowToRemove) {
                continue;
            }

            $actualCol = 0;

            for ($currentColumn = 0; $currentColumn < $this->columnCount; $currentColumn++) {
                if ($currentColumn === $columnToRemove) {
                    continue;
                }

                $result[$actualRow][$actualCol] = $this->rowData[$currentRow][$currentColumn];
                $actualCol++;
            }

            $actualRow++;
        }

        return new Matrix($this->rowCount - 1, $this->columnCount - 1, $result);
    }

    public function getCofactor(int $rowToRemove, int $columnToRemove): float
    {
        // See http://en.wikipedia.org/wiki/Cofactor_(linear_algebra) for details
        $isEven = (($rowToRemove + $columnToRemove) % 2) === 0;
        $minorDeterminant = $this->getMinorMatrix($rowToRemove, $columnToRemove)->getDeterminant();

        return $isEven ? $minorDeterminant : -1.0 * $minorDeterminant;
    }

    /**
     * Whether both matrices have the same size and every value is within ERROR_TOLERANCE.
     */
    public function equals(Matrix $otherMatrix): bool
    {
        if ($this->rowCount !== $otherMatrix->getRowCount() || $this->columnCount !== $otherMatrix->getColumnCount()) {
            return false;
        }

        for ($currentRow = 0; $currentRow < $this->rowCount; $currentRow++) {
            for ($currentColumn = 0; $currentColumn < $this->columnCount; $currentColumn++) {
                $delta = abs($this->rowData[$currentRow][$currentColumn] - $otherMatrix->getValue($currentRow, $currentColumn));

                if ($delta > self::ERROR_TOLERANCE) {
                    return false;
                }
            }
        }

        return true;
    }
}
