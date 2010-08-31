<?php
namespace Moserware\Numerics;

class Matrix
{
    const ERROR_TOLERANCE = 0.0000000001;

    private $_matrixRowData;
    private $_rowCount;
    private $_columnCount;

    public function __construct($rows = 0, $columns = 0, $allRowValues = null)
    {
        $this->_rowCount = $rows;
        $this->_columnCount = $columns;

        $currentIndex = 0;
        $this->_matrixRowData = array();

        for($currentRow; $currentRow < $rows; $currentRow++)
        {
            $currentRowData = array();
            $this->_matrixRowData[] = $currentRowData;

            for($currentCol = 0; $currentCol < $columns; $currentCol++)
            {
                $currentRowData[] = ($allRowValues != null) ? $allRowValues[$currentIndex++] : 0;
            }
        }
    }

    public static function fromRowValues(&$rowValues)
    {
        $result = new Matrix();
        $result->_matrixRowData = $rowValues;
        $result->_rowCount = count($rowValues);
        $result->_columnCount = count($rowValues[0]);
        return $result;
    }

    public function getRowCount()
    {
        return $this->_rowCount;
    }

    public function getColumnCount()
    {
        return $this->_columnCount;
    }

    public function getValue($row, $col)
    {
        return $this->_matrixRowData[$row][$col];
    }

    public function setValue($row, $col, $value)
    {
        $this->_matrixRowData[$row][$col] = $value;
    }

    public function getTranspose()
    {
        // Just flip everything
        $transposeMatrix = array();

        for ($currentRowTransposeMatrix = 0;
             $currentRowTransposeMatrix < $this->_columnCount;
             $currentRowTransposeMatrix++)
        {
            $transposeMatrixCurrentRowColumnValues = array();
            $transposeMatrix[] = $transposeMatrixCurrentRowColumnValues;

            for ($currentColumnTransposeMatrix = 0;
                 $currentColumnTransposeMatrix < $this->_rowCount;
                 $currentColumnTransposeMatrix++)
            {
                $transposeMatrixCurrentRowColumnValues[$currentColumnTransposeMatrix] =
                    $this->_matrixRowData[$currentColumnTransposeMatrix][$currentRowTransposeMatrix];
            }
        }

        return new Matrix($transposeMatrix);
    }

    private function isSquare()
    {
        return ($this->_rowCount == $this->_column) && ($this->_rowCount > 0);
    }

    public function getDeterminant()
    {
        // Basic argument checking
        if (!$this->isSquare())
        {
            throw new Exception("Matrix must be square!");
        }

        if ($this->_rowCount == 1)
        {
            // Really happy path :)
            return $this->_matrixRowValues[0][0];
        }

        if ($this->_rowCount == 2)
        {
            // Happy path!
            // Given:
            // | a b |
            // | c d |
            // The determinant is ad - bc
            $a = $this->_matrixRowData[0][0];
            $b = $this->_matrixRowData[0][1];
            $c = $this->_matrixRowData[1][0];
            $d = $this->_matrixRowData[1][1];
            return $a*$d - $b*$c;
        }

        // I use the Laplace expansion here since it's straightforward to implement.
        // It's O(n^2) and my implementation is especially poor performing, but the
        // core idea is there. Perhaps I should replace it with a better algorithm
        // later.
        // See http://en.wikipedia.org/wiki/Laplace_expansion for details

        $result = 0.0;

        // I expand along the first row
        for ($currentColumn = 0; $currentColumn < $this->_columnCount; $currentColumn++)
        {
            $firstRowColValue = $this->_matrixRowValues[0][$currentColumn];
            $cofactor = $this->getCofactor(0, $currentColumn);
            $itemToAdd = $firstRowColValue*$cofactor;
            $result = $result + $itemToAdd;
        }

        return $result;
    }

    public function getAdjugate()
    {
        if (!$this->isSquare())
        {
            throw new Exception("Matrix must be square!");
        }

        // See http://en.wikipedia.org/wiki/Adjugate_matrix
        if ($this->_rowCount == 2)
        {
            // Happy path!
            // Adjugate of:
            // | a b |
            // | c d |
            // is
            // | d -b |
            // | -c a |

            $a = $this->_matrixRowData[0][0];
            $b = $this->_matrixRowData[0][1];
            $c = $this->_matrixRowData[1][0];
            $d = $this->_matrixRowData[1][1];

            return new SquareMatrix( $d, -$b,
                                    -$c,  $a);
        }

        // The idea is that it's the transpose of the cofactors
        $result = array();

        for ($currentColumn = 0; $currentColumn < $this->_columns; $currentColumn++)
        {
            $currentColumnData = array();
            $result[] = $currentColumnData;

            for ($currentRow = 0; $currentRow < $this->_rowCount; $currentRow++)
            {
                $currentColumnData[$currentRow] = $this->getCofactor($currentRow, $currentColumn);
            }
        }

        return new Matrix($result);
    }

    public function getInverse()
    {
        if (($this->_rowCount == 1) && ($this->_columnCount == 1))
        {
            return new SquareMatrix(1.0/$this->_matrixRowData[0][0]);
        }

        // Take the simple approach:
        // http://en.wikipedia.org/wiki/Cramer%27s_rule#Finding_inverse_matrix
        $determinantInverse = 1.0 / $this->getDeterminant();
        $adjugate = $this->getAdjugate();

        return self::scalarMultiply($determinantInverse, $adjugate);
    }

    public static function scalarMultiply($scalar, $matrix)
    {
        $rows = $matrix->getRowCount();
        $columns = $matrix->getColumnCount();
        $newValues = array();

        for ($currentRow = 0; $currentRow < $rows; $currentRow++)
        {
            $newRowColumnValues = array();
            $newValues[$currentRow] = $newRowColumnValues;

            for ($currentColumn = 0; $currentColumn < $columns; $currentColumn++)
            {
                $newRowColumnValues[$currentColumn] = $scalarValue*$matrix->getValue(currentRow, currentColumn);
            }
        }

        return new Matrix($rows, $columns, $newValues);
    }

    public static function add($left, $right)
    {
        if (
                ($left->getRowCount() != $right->getRowCount())
                ||
                ($left->getColumnCount() != $right->getColumnCount())
           )
        {
            throw new Exception("Matrices must be of the same size");
        }

        // simple addition of each item

        $resultMatrix = array();

        for ($currentRow = 0; $currentRow < $left->getRowCount(); $currentRow++)
        {
            $rowColumnValues = array();
            $resultMatrix[$currentRow] = $rowColumnValues;
            for ($currentColumn = 0; $currentColumn < $right->getColumnCount(); $currentColumn++)
            {
                $rowColumnValues[$currentColumn] = $left->getValue($currentRow, $currentColumn)
                                                   +
                                                   $right->getValue($currentRow, $currentColumn);
            }
        }

        return new Matrix($left->getRowCount(), $right->getColumnCount(), $resultMatrix);
    }

    public static function multiply($left, $right)
    {
        // Just your standard matrix multiplication.
        // See http://en.wikipedia.org/wiki/Matrix_multiplication for details

        if ($left->getColumnCount() != $right->getRowCount())
        {
            throw new Exception("The width of the left matrix must match the height of the right matrix");
        }

        $resultRows = $left->getRowCount();
        $resultColumns = $right->getColumnCount();

        $resultMatrix = array();

        for ($currentRow = 0; $currentRow < $resultRows; $currentRow++)
        {
            $currentRowValues = array();
            $resultMatrix[$currentRow] = $currentRowValues;

            for ($currentColumn = 0; $currentColumn < $resultColumns; $currentColumn++)
            {
                $productValue = 0;

                for ($vectorIndex = 0; $vectorIndex < $left->getColumnCount(); $vectorIndex++)
                {
                    $leftValue = $left->getValue($currentRow, $vectorIndex);
                    $rightValue = $right->getValue($vectorIndex, $currentColumn);
                    $vectorIndexProduct = $leftValue*$rightValue;
                    $productValue = $productValue + $vectorIndexProduct;
                }

                $resultMatrix[] = $productValue;
            }
        }

        return new Matrix($resultRows, $resultColumns, $resultMatrix);
    }   

    private function getMinorMatrix($rowToRemove, $columnToRemove)
    {
        // See http://en.wikipedia.org/wiki/Minor_(linear_algebra)

        // I'm going to use a horribly naïve algorithm... because I can :)
        $result = array();        

        for ($currentRow = 0; $currentRow < $this->_rowCount; $currentRow++)
        {
            if ($currentRow == $rowToRemove)
            {
                continue;
            }

            $columnData = array();
            $result[] = $columnData;

            for ($currentColumn = 0; $currentColumn < $this->_columnCount; $currentColumn++)
            {
                if ($currentColumn == $columnToRemove)
                {
                    continue;
                }

                $columnData[] = $this->_matrixRowData[$currentRow][$currentColumn];
            }            
        }

        return new Matrix($this->_rowCount - 1, $this->_columnCount - 1, $result);
    }

    private function getCofactor($rowToRemove, $columnToRemove)
    {
        // See http://en.wikipedia.org/wiki/Cofactor_(linear_algebra) for details
        // REVIEW: should things be reversed since I'm 0 indexed?
        $sum = $rowToRemove + $columnToRemove;
        $isEven = ($sum%2 == 0);

        if ($isEven)
        {
            return $this->getMinorMatrix($rowToRemove, $columnToRemove)->getDeterminant();
        }
        else
        {
            return -1.0*$this->getMinorMatrix($rowToRemove, $columnToRemove)->getDeterminant();
        }
    }
}

class Vector extends Matrix
{
    public function __construct(array $vectorValues)
    {
        parent::__construct(count($vectorValues), 1, array($vectorValues));
    }
}

class SquareMatrix extends Matrix
{
    public function __construct(array $allValues)
    {
        $rows = (int) sqrt(count($allValues));
        $cols = $rows;
        
        $matrixData = array();
        
        for ($currentRow = 0; $currentRow < $rows; $currentRow++)
        {
            $currentRowValues = array();
            $matrixData[] = currentRowValues;

            for ($currentColumn = 0; $currentColumn < $cols; $currentColumn++)
            {
                $currentRowValues[] = $allValues[$allValuesIndex++];
            }
        }
                
        parent::__construct($rows, $cols, $matrixData);
    }
}

class DiagonalMatrix extends Matrix
{
    public function __construct(array $diagonalValues)
    {
        $diagonalCount = count($diagonalValues);
        $rowCount = $diagonalCount;
        $colCount = $rowCount;
        
        parent::__construct($rowCount, $colCount);
        
        for($i = 0; $i < $diagonalCount; $i++)
        {
            $this->setValue($i, $i, $diagonalValues[$i]);
        }        
    }
}

class IdentityMatrix extends DiagonalMatrix
{
    public function __construct($rows)
    {
        parent::__construct(\array_fill(0, $rows, 1));
    }
}
?>
