<?php

declare(strict_types=1);

namespace App\Web\Inventory\Stock;

use App\Catalog\CatalogRepository;
use App\Inventory\InventoryRepository;
use DomainException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

use function is_array;
use function trim;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
        private InventoryRepository $inventoryRepository,
        private CatalogRepository $catalogRepository,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $query = $request->getQueryParams();
        $selectedLocationId = isset($query['location']) && $query['location'] !== '' ? (int) $query['location'] : null;
        $form = [
            'location_id' => $selectedLocationId === null ? '' : (string) $selectedLocationId,
            'item_id' => '',
            'quantity' => '0',
        ];

        if ($request->getMethod() === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];
            $form = [
                'location_id' => trim((string) ($data['location_id'] ?? '')),
                'item_id' => trim((string) ($data['item_id'] ?? '')),
                'quantity' => trim((string) ($data['quantity'] ?? '0')),
            ];

            try {
                if (($data['operation'] ?? 'save') === 'delete') {
                    $this->inventoryRepository->deleteStockRow((int) ($data['id'] ?? 0));
                    return $this->redirect([
                        'status' => 'stock-deleted',
                        'location' => (string) ($data['location_filter'] ?? ''),
                    ]);
                }

                if ($form['location_id'] === '' || $form['item_id'] === '') {
                    throw new DomainException('Lokasi dan item wajib dipilih.');
                }

                $quantity = (int) $form['quantity'];
                if ($quantity < 0) {
                    throw new DomainException('Quantity stok tidak boleh negatif.');
                }

                $this->inventoryRepository->setStock((int) $form['location_id'], (int) $form['item_id'], $quantity);

                return $this->redirect([
                    'status' => 'stock-saved',
                    'location' => $form['location_id'],
                ]);
            } catch (DomainException $exception) {
                $errors[] = $exception->getMessage();
                $selectedLocationId = $form['location_id'] === '' ? null : (int) $form['location_id'];
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'errors' => $errors,
            'status' => (string) ($query['status'] ?? ''),
            'selectedLocationId' => $selectedLocationId,
            'form' => $form,
            'locations' => $this->inventoryRepository->getLocations(),
            'items' => $this->catalogRepository->getItems(),
            'stockRows' => $this->inventoryRepository->getStockRows($selectedLocationId),
        ]);
    }

    /**
     * @param array<string,string> $queryParameters
     */
    private function redirect(array $queryParameters): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('inventory.stock', [], $queryParameters));
    }
}
