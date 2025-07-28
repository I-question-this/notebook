<?php

declare(strict_types=1);

namespace OCA\NoteBook\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string|null getUserId()
 * @method void setUserId(?string $userId)
 * @method string getName()
 * @method void setName(string $name)
 * @method string getContent()
 * @method void setContent(string $content)
 * @method int getLastModified()
 * @method void setLastModified(int $lastModified)
 */
class Note extends Entity implements \JsonSerializable {

	/** @var string */
	protected $userId;
	/** @var string */
	protected $name;
	/** @var string */
	protected $content;
	/** @var int */
	protected $lastModified;

	public function __construct() {
		$this->addType('userId', 'string');
		$this->addType('name', 'string');
		$this->addType('content', 'string');
		$this->addType('lastModified', 'integer');
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'user_id' => $this->userId,
			'name' => $this->name,
			'content' => $this->content,
			'last_modified' => (int) $this->lastModified,
		];
	}
}