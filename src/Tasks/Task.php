<?php

namespace PropMgr\Tasks;

abstract class Task
{		
	protected int $id;
	protected array $args;
	protected array $state;
	protected int $created;
	protected bool $is_started;
	protected bool $is_finished;
		
	public function __construct(
		int $id, 
		int $created,
		?array $args=[], 
		?array $state = null, 
		?bool $is_started = false, 
		?bool $is_finished = false
	) {
		$this->id = $id;
		$this->created = $created;
		$this->args = $args;
		if (empty($state)) {
			$state = $this->getDefaultState($args);
		}
		$this->state = $state;
		$this->is_started = $is_started;
		$this->is_finished = $is_finished;
	}
	
	public abstract function getDefaultState(array $args): array;
	public abstract function doNext();
	
	public function getId(): int { return $this->id; }
	public function getArgs(): array { return $this->args; }
	public function getState(): array { return $this->state; }
	public function getCreated(): int { return $this->created; }
	public function getIsStarted(): bool { return $this->is_started; }
	public function getIsFinished(): bool { return $this->is_finished; }
	 
}