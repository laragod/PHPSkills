<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

use Laragod\Skills\Guard;
use LogicException;
use Stringable;

/**
 * A factor connects variables and sends them messages.
 *
 * The i-th message always belongs to the i-th variable.
 *
 * @template TValue
 */
abstract class Factor implements Stringable
{
    /** @var list<Message<TValue>> */
    private array $messages = [];

    /** @var list<Variable<TValue>> */
    private array $variables = [];

    private readonly string $name;

    protected function __construct(string $name)
    {
        $this->name = 'Factor['.$name.']';
    }

    /**
     * The log-normalization constant of this factor.
     */
    public function getLogNormalization(): float
    {
        return 0.0;
    }

    /**
     * The number of messages (and therefore variables) the factor has.
     */
    public function getNumberOfMessages(): int
    {
        return count($this->messages);
    }

    /**
     * @return list<Variable<TValue>>
     */
    protected function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return list<Message<TValue>>
     */
    protected function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Updates the message and marginal of the i-th variable the factor is connected to.
     *
     * @return float The change in the variable's marginal.
     */
    public function updateMessageIndex(int $messageIndex): float
    {
        Guard::argumentIsValidIndex($messageIndex, count($this->messages), 'messageIndex');

        return $this->updateMessageVariable($this->messages[$messageIndex], $this->variables[$messageIndex]);
    }

    /**
     * @param  Message<TValue>  $message
     * @param  Variable<TValue>  $variable
     * @return float The change in the variable's marginal.
     */
    protected function updateMessageVariable(Message $message, Variable $variable): float
    {
        throw new LogicException(static::class.' does not support updating a single message');
    }

    /**
     * Resets the marginals of the variables the factor is connected to.
     */
    public function resetMarginals(): void
    {
        foreach ($this->variables as $variable) {
            $variable->resetToPrior();
        }
    }

    /**
     * Sends the i-th message to the marginal and returns the log-normalization constant.
     */
    public function sendMessageIndex(int $messageIndex): float
    {
        Guard::argumentIsValidIndex($messageIndex, count($this->messages), 'messageIndex');

        return $this->sendMessageVariable($this->messages[$messageIndex], $this->variables[$messageIndex]);
    }

    /**
     * @param  Message<TValue>  $message
     * @param  Variable<TValue>  $variable
     * @return float The log-normalization constant.
     */
    abstract protected function sendMessageVariable(Message $message, Variable $variable): float;

    /**
     * @param  Variable<TValue>  $variable
     * @return Message<TValue>
     */
    abstract public function createVariableToMessageBinding(Variable $variable): Message;

    /**
     * @param  Variable<TValue>  $variable
     * @param  Message<TValue>  $message
     * @return Message<TValue>
     */
    protected function createVariableToMessageBindingWithMessage(Variable $variable, Message $message): Message
    {
        $this->messages[] = $message;
        $this->variables[] = $variable;

        return $message;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
