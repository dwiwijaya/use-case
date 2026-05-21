<?php

declare(strict_types=1);

namespace App\Web\Inventory\Locations;

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
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $query = $request->getQueryParams();
        $form = ['id' => '', 'code' => '', 'name' => ''];

        if (isset($query['edit'])) {
            $form = $this->inventoryRepository->findLocation((int) $query['edit']) ?? $form;
        }

        if ($request->getMethod() === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];
            $form = [
                'id' => trim((string) ($data['id'] ?? '')),
                'code' => trim((string) ($data['code'] ?? '')),
                'name' => trim((string) ($data['name'] ?? '')),
            ];

            try {
                if (($data['operation'] ?? 'save') === 'delete') {
                    $this->inventoryRepository->deleteLocation((int) $form['id']);
                    return $this->redirect(['status' => 'location-deleted']);
                }

                if ($form['code'] === '' || $form['name'] === '') {
                    throw new DomainException('Kode dan nama lokasi wajib diisi.');
                }

                $this->inventoryRepository->saveLocation(
                    $form['id'] === '' ? null : (int) $form['id'],
                    $form['code'],
                    $form['name']
                );

                return $this->redirect(['status' => $form['id'] === '' ? 'location-created' : 'location-updated']);
            } catch (DomainException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'errors' => $errors,
            'status' => (string) ($query['status'] ?? ''),
            'form' => $form,
            'locations' => $this->inventoryRepository->getLocations(),
        ]);
    }

    /**
     * @param array<string,string> $queryParameters
     */
    private function redirect(array $queryParameters): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('inventory.locations', [], $queryParameters));
    }
}
