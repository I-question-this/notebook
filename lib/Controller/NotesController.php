<?php

declare(strict_types=1);

namespace OCA\NoteBook\Controller;

use Exception;
use OCA\NoteBook\Db\NoteMapper;
use OCA\NoteBook\Service\NoteService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;
use Throwable;

class NotesController extends OCSController {

	public const REQUIREMENTS = [
		'apiVersion' => 'v1',
	];

	public function __construct(
		string             $appName,
		IRequest           $request,
		private NoteMapper $noteMapper,
		private NoteService $noteService,
		private ?string    $userId
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/{apiVersion}/notes', requirements: self::REQUIREMENTS)]
	public function getUserNotes(): DataResponse {
		try {
			return new DataResponse($this->noteMapper->getNotesOfUser($this->userId));
		} catch (Exception | Throwable $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @param string $name
	 * @param string $content
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/{apiVersion}/notes', requirements: self::REQUIREMENTS)]
	public function addUserNote(string $name, string $content = ''): DataResponse {
		try {
			$note= $this->noteMapper->createNote($this->userId, $name, $content);
			return new DataResponse($note);
		} catch (Exception | Throwable $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @param int $id
	 * @param string|null $name
	 * @param string|null $content
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/{apiVersion}/notes/{id}', requirements: self::REQUIREMENTS)]
	public function editUserNote(int $id, ?string $name = null, ?string $content = null): DataResponse {
		try {
			$note = $this->noteMapper->updateNote($id, $this->userId, $name, $content);
			return new DataResponse($note);
		} catch (Exception | Throwable $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @param int $id
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/{apiVersion}/notes/{id}', requirements: self::REQUIREMENTS)]
	public function deleteUserNote(int $id): DataResponse {
		try {
			$note = $this->noteMapper->deleteNote($id, $this->userId);
			return new DataResponse($note);
		} catch (Exception | Throwable $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @param int $id
	 * @return DataResponse
	 */
	#[ApiRoute(verb: 'GET', url: '/api/{apiVersion}/notes/{id}/export', requirements: self::REQUIREMENTS)]
	public function exportUserNote(int $id): DataResponse {
		try {
			$path = $this->noteService->exportNote($id, $this->userId);
			return new DataResponse($path);
		} catch (Exception | Throwable $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}
}