<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

use Exception;
use Laragod\Skills\Guard;
use Laragod\Skills\HashMap;

abstract class Factor
{
    private $_messages = [];

    private \Laragod\Skills\HashMap $_messageToVariableBinding;

    private string $_name;

    private $_variables = [];

    protected function __construct($name)
    {
        $this->_name = 'Factor['.$name.']';
        $this->_messageToVariableBinding = new HashMap();
    }

    /**
     * @return mixed The log-normalization constant of that factor
     */
    public function getLogNormalization() : mixed
    {
        return 0;
    }

    /**
     * @return int The number of messages that the factor has
     */
    public function getNumberOfMessages(): int
    {
        return count($this->_messages);
    }

    protected function getVariables(): array
    {
        return $this->_variables;
    }

    protected function getMessages(): array
    {
        return $this->_messages;
    }

    /**
     * Update the message and marginal of the i-th variable that the factor is connected to
     *
     * @throws Exception
     */
    public function updateMessageIndex($messageIndex)
    {
        Guard::argumentIsValidIndex($messageIndex, count($this->_messages), 'messageIndex');
        $message = $this->_messages[$messageIndex];
        $variable = $this->_messageToVariableBinding->getValue($message);

        return $this->updateMessageVariable($message, $variable);
    }

    protected function updateMessageVariable(Message $message, Variable $variable)
    {
        throw new Exception();
    }

    /**
     * Resets the marginal of the variables a factor is connected to
     */
    public function resetMarginals(): void
    {
        $allValues = $this->_messageToVariableBinding->getAllValues();
        foreach ($allValues as $currentVariable) {
            $currentVariable->resetToPrior();
        }
    }

    /**
     * Sends the ith message to the marginal and returns the log-normalization constant
     *
     * @throws Exception
     */
    public function sendMessageIndex($messageIndex)
    {
        Guard::argumentIsValidIndex($messageIndex, count($this->_messages), 'messageIndex');

        $message = $this->_messages[$messageIndex];
        $variable = $this->_messageToVariableBinding->getValue($message);

        return $this->sendMessageVariable($message, $variable);
    }

    abstract protected function sendMessageVariable(Message $message, Variable $variable);

    abstract public function createVariableToMessageBinding(Variable $variable);

    protected function createVariableToMessageBindingWithMessage(Variable $variable, Message $message): Message
    {
        $this->_messageToVariableBinding->setValue($message, $variable);
        $this->_messages[] = $message;
        $this->_variables[] = $variable;

        return $message;
    }

    public function __toString()
    {
        return $this->_name;
    }
}
