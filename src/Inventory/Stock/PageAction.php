<?php

declare(strict_types=1);

namespace App\Inventory\Stock;

use App\Catalog\CatalogViewRepositoryInterface;
use App\Inventory\InventoryViewRepositoryInterface;
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
        private CatalogViewRepositoryInterface $catalogViewRepository,
        private StockService $stockService,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $query = $request->getQueryParams();
        $selectedLocationId = isset($query['location']) && $query['location'] !== '' ? (int) $query['location'] : null;
        $form = StockInput::withLocationId($selectedLocationId);

        if ($request->getMethod() === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];

            try {
                if (($data['operation'] ?? 'save') === 'delete') {
                    $selectedLocationId = isset($data['currentLocation']) && $data['currentLocation'] !== ''
                        ? (int) $data['currentLocation']
                        : $selectedLocationId;

                    $deleteInput = new IdentifierInput();
                    if ($this->formHydrator->populateFromPostAndValidate($deleteInput, $request, scope: 'stockDelete')) {
                        $this->stockService->delete($deleteInput->toId());
                        $status = 'stock-deleted';
                        return $this->render(['status' => $status, 'location' => $selectedLocationId], StockInput::withLocationId($selectedLocationId), $errors, $selectedLocationId);
                    }
                    $errors = $deleteInput->isValidated()
                        ? $deleteInput->getValidationResult()->getErrorMessages()
                        : ['Payload stok tidak valid.'];
                } else {
                    $this->formHydrator->populateFromPostAndValidate($form, $request, scope: 'stock');
                    if ($form->isValid()) {
                        $selectedLocationId = (int) $form->locationId;
                        $this->stockService->save($form);
                        return $this->render(
                            ['status' => 'stock-saved', 'location' => $selectedLocationId],
                            StockInput::withLocationId($selectedLocationId),
                            [],
                            $selectedLocationId,
                        );
                    }

                    $selectedLocationId = $form->locationId === '' ? null : (int) $form->locationId;
                }
            } catch (DomainException $exception) {
                $errors[] = $exception->getMessage();
                $selectedLocationId = $form->locationId === '' ? null : (int) $form->locationId;
            }
        }

        return $this->render($query, $form, $errors, $selectedLocationId);
    }

    /**
     * @param array<string,mixed> $query
     * @param list<string> $errors
     */
    private function render(array $query, StockInput $form, array $errors, ?int $selectedLocationId): ResponseInterface
    {
        return $this->viewRenderer->render(__DIR__ . '/template', [
            'errors' => $errors,
            'status' => (string) ($query['status'] ?? ''),
            'selectedLocationId' => $selectedLocationId,
            'form' => $form,
            'locations' => $this->inventoryViewRepository->getLocations(),
            'items' => $this->catalogViewRepository->getItems(),
            'stockRows' => $this->inventoryViewRepository->getStockRows($selectedLocationId),
        ]);
    }
}
