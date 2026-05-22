<?php

declare(strict_types=1);

namespace App\Catalog;

use App\Catalog\Item\ItemInput;
use App\Catalog\Item\ItemRepositoryInterface;
use App\Catalog\Item\ItemService;
use App\Catalog\Unit\UnitInput;
use App\Catalog\Unit\UnitRepositoryInterface;
use App\Catalog\Unit\UnitService;
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
        private CatalogViewRepositoryInterface $catalogViewRepository,
        private UnitRepositoryInterface $units,
        private ItemRepositoryInterface $items,
        private UnitService $unitService,
        private ItemService $itemService,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $query = $request->getQueryParams();
        $unitForm = new UnitInput();
        $itemForm = new ItemInput();

        if (isset($query['editUnit'])) {
            $unit = $this->units->findById((int) $query['editUnit']);
            if ($unit !== null) {
                $unitForm = UnitInput::fromEntity($unit);
            }
        }

        if (isset($query['editItem'])) {
            $item = $this->items->findById((int) $query['editItem']);
            if ($item !== null) {
                $itemForm = ItemInput::fromEntity($item);
            }
        }

        if ($request->getMethod() === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];
            $entity = (string) ($data['entity'] ?? '');
            $operation = (string) ($data['operation'] ?? 'save');

            try {
                if ($entity === 'unit') {
                    if ($operation === 'delete') {
                        $deleteInput = new IdentifierInput();
                        if ($this->formHydrator->populateFromPostAndValidate($deleteInput, $request, scope: 'unit')) {
                            $this->unitService->delete($deleteInput->toId());
                            return $this->render(['status' => 'unit-deleted'], new UnitInput(), new ItemInput());
                        }
                        $errors = $deleteInput->isValidated()
                            ? $deleteInput->getValidationResult()->getErrorMessages()
                            : ['Payload unit tidak valid.'];
                    } else {
                        $this->formHydrator->populateFromPostAndValidate($unitForm, $request, scope: 'unit');
                        if ($unitForm->isValid()) {
                            $isCreate = $unitForm->id === '';
                            $this->unitService->save($unitForm);
                            return $this->render(
                                ['status' => $isCreate ? 'unit-created' : 'unit-updated'],
                                new UnitInput(),
                                new ItemInput(),
                            );
                        }
                    }
                }

                if ($entity === 'item') {
                    if ($operation === 'delete') {
                        $deleteInput = new IdentifierInput();
                        if ($this->formHydrator->populateFromPostAndValidate($deleteInput, $request, scope: 'item')) {
                            $this->itemService->delete($deleteInput->toId());
                            return $this->render(['status' => 'item-deleted'], new UnitInput(), new ItemInput());
                        }
                        $errors = $deleteInput->isValidated()
                            ? $deleteInput->getValidationResult()->getErrorMessages()
                            : ['Payload item tidak valid.'];
                    } else {
                        $this->formHydrator->populateFromPostAndValidate($itemForm, $request, scope: 'item');
                        if ($itemForm->isValid()) {
                            $isCreate = $itemForm->id === '';
                            $this->itemService->save($itemForm);
                            return $this->render(
                                ['status' => $isCreate ? 'item-created' : 'item-updated'],
                                new UnitInput(),
                                new ItemInput(),
                            );
                        }
                    }
                }
            } catch (DomainException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        return $this->render($query, $unitForm, $itemForm, $errors);
    }

    /**
     * @param array<string,mixed> $query
     * @param list<string> $errors
     */
    private function render(array $query, UnitInput $unitForm, ItemInput $itemForm, array $errors = []): ResponseInterface
    {
        return $this->viewRenderer->render(__DIR__ . '/template', [
            'errors' => $errors,
            'status' => (string) ($query['status'] ?? ''),
            'units' => $this->catalogViewRepository->getUnits(),
            'items' => $this->catalogViewRepository->getItems(),
            'unitForm' => $unitForm,
            'itemForm' => $itemForm,
        ]);
    }
}
