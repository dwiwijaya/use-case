<?php

declare(strict_types=1);

namespace App\Inventory\Location\Action;

use App\Inventory\Location\Domain\LocationInput;
use App\Inventory\Location\Domain\LocationRepositoryInterface;
use App\Inventory\Location\Domain\LocationService;
use App\Inventory\ReadModel\InventoryViewRepositoryInterface;
use App\Shared\Form\IdentifierInput;
use DomainException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

use function is_array;

final readonly class PageAction
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private FormHydrator $formHydrator,
        private InventoryViewRepositoryInterface $inventoryViewRepository,
        private LocationRepositoryInterface $locations,
        private LocationService $locationService,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $query = $request->getQueryParams();
        $form = new LocationInput();

        if (isset($query['edit'])) {
            $location = $this->locations->findById((int) $query['edit']);
            if ($location !== null) {
                $form = LocationInput::fromEntity($location);
            }
        }

        if ($request->getMethod() === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];

            try {
                if (($data['operation'] ?? 'save') === 'delete') {
                    $deleteInput = new IdentifierInput();
                    if ($this->formHydrator->populateFromPostAndValidate($deleteInput, $request, scope: 'location')) {
                        $this->locationService->delete($deleteInput->toId());
                        return $this->render(['status' => 'location-deleted'], new LocationInput());
                    }
                    $errors = $deleteInput->isValidated()
                        ? $deleteInput->getValidationResult()->getErrorMessages()
                        : ['Payload lokasi tidak valid.'];
                } else {
                    $this->formHydrator->populateFromPostAndValidate($form, $request, scope: 'location');
                    if ($form->isValid()) {
                        $isCreate = $form->id === '';
                        $this->locationService->save($form);
                        return $this->render(
                            ['status' => $isCreate ? 'location-created' : 'location-updated'],
                            new LocationInput(),
                        );
                    }
                }
            } catch (DomainException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        return $this->render($query, $form, $errors);
    }

    /**
     * @param array<string,mixed> $query
     * @param list<string> $errors
     */
    private function render(array $query, LocationInput $form, array $errors = []): ResponseInterface
    {
        return $this->viewRenderer->render(__DIR__ . '/../Presentation/Web/template', [
            'errors' => $errors,
            'status' => (string) ($query['status'] ?? ''),
            'form' => $form,
            'locations' => $this->inventoryViewRepository->getLocations(),
        ]);
    }
}
