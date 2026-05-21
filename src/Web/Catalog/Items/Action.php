<?php

declare(strict_types=1);

namespace App\Web\Catalog\Items;

use App\Catalog\CatalogRepository;
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
        private CatalogRepository $catalogRepository,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $query = $request->getQueryParams();
        $unitForm = ['id' => '', 'name' => '', 'symbol' => ''];
        $itemForm = ['id' => '', 'sku' => '', 'name' => '', 'unit_id' => ''];

        if (isset($query['editUnit'])) {
            $unitForm = $this->catalogRepository->findUnit((int) $query['editUnit']) ?? $unitForm;
        }
        if (isset($query['editItem'])) {
            $itemForm = $this->catalogRepository->findItem((int) $query['editItem']) ?? $itemForm;
        }

        if ($request->getMethod() === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];
            $entity = (string) ($data['entity'] ?? '');
            $operation = (string) ($data['operation'] ?? 'save');

            try {
                if ($entity === 'unit') {
                    $unitForm = [
                        'id' => trim((string) ($data['id'] ?? '')),
                        'name' => trim((string) ($data['name'] ?? '')),
                        'symbol' => trim((string) ($data['symbol'] ?? '')),
                    ];

                    if ($operation === 'delete') {
                        $this->catalogRepository->deleteUnit((int) $unitForm['id']);
                        return $this->redirect(['status' => 'unit-deleted']);
                    }

                    if ($unitForm['name'] === '' || $unitForm['symbol'] === '') {
                        throw new DomainException('Nama dan simbol unit wajib diisi.');
                    }

                    $this->catalogRepository->saveUnit(
                        $unitForm['id'] === '' ? null : (int) $unitForm['id'],
                        $unitForm['name'],
                        $unitForm['symbol']
                    );

                    return $this->redirect(['status' => $unitForm['id'] === '' ? 'unit-created' : 'unit-updated']);
                }

                if ($entity === 'item') {
                    $itemForm = [
                        'id' => trim((string) ($data['id'] ?? '')),
                        'sku' => trim((string) ($data['sku'] ?? '')),
                        'name' => trim((string) ($data['name'] ?? '')),
                        'unit_id' => trim((string) ($data['unit_id'] ?? '')),
                    ];

                    if ($operation === 'delete') {
                        $this->catalogRepository->deleteItem((int) $itemForm['id']);
                        return $this->redirect(['status' => 'item-deleted']);
                    }

                    if ($itemForm['sku'] === '' || $itemForm['name'] === '' || $itemForm['unit_id'] === '') {
                        throw new DomainException('SKU, nama item, dan unit wajib diisi.');
                    }

                    $this->catalogRepository->saveItem(
                        $itemForm['id'] === '' ? null : (int) $itemForm['id'],
                        $itemForm['sku'],
                        $itemForm['name'],
                        (int) $itemForm['unit_id']
                    );

                    return $this->redirect(['status' => $itemForm['id'] === '' ? 'item-created' : 'item-updated']);
                }
            } catch (DomainException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'errors' => $errors,
            'status' => (string) ($query['status'] ?? ''),
            'units' => $this->catalogRepository->getUnits(),
            'items' => $this->catalogRepository->getItems(),
            'unitForm' => $unitForm,
            'itemForm' => $itemForm,
        ]);
    }

    /**
     * @param array<string,string> $queryParameters
     */
    private function redirect(array $queryParameters): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('catalog.items', [], $queryParameters));
    }
}
