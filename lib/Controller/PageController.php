<?php

declare(strict_types=1);

namespace OCA\NoteBook\Controller;

use OCA\NoteBook\AppInfo\Application;
// use OCA\NoteBook\Db\NoteMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Collaboration\Reference\RenderReferenceEvent;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IRequest;
use OCP\PreConditionNotMetException;

class PageController extends Controller {

	public function __construct(
		string   $appName,
		IRequest $request,
		private IEventDispatcher $eventDispatcher,
		private IInitialState $initialStateService,
		private IConfig $config,
//		private NoteMapper $noteMapper,
		private ?string $userId
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @return TemplateResponse
	 * @throws PreConditionNotMetException
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	#[FrontpageRoute(verb: 'GET', url: '/')]
	public function index(): TemplateResponse {
		$this->eventDispatcher->dispatchTyped(new RenderReferenceEvent());
// 		try {
// 			$notes = $this->noteMapper->getNotesOfUser($this->userId);
// 		} catch (\Exception | \Throwable $e) {
// 			$notes = [];
// 		}
		$notes = [];
		$selectedNoteId = (int) $this->config->getUserValue($this->userId, Application::APP_ID, 'selected_note_id', '0');
		$state = [
			'notes' => $notes,
			'selected_note_id' => $selectedNoteId,
		];
		$this->initialStateService->provideInitialState('notes-initial-state', $state);
		return new TemplateResponse(Application::APP_ID, 'index');
	}
}