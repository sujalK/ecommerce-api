<?php

declare(strict_types = 1);

namespace App\Service\Product\Post;

use App\Contracts\EnvironmentVariablesServiceInterface;
use App\Contracts\HttpResponseInterface;
use App\DataObjects\FileUploadData;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Enum\ActivityLog;
use App\Enum\EnvVars;
use App\Exception\AuthenticationException;
use App\Exception\FileNotFoundException;
use App\Exception\InvalidCategoryException;
use App\Exception\InvalidFileException;
use App\Exception\InvalidProductInfoException;
use App\Exception\UnauthorizedException;
use App\Repository\ProductCategoryRepository;
use App\Service\ActivityLogService;
use App\Service\AuthService;
use App\Service\FileUploaderService;
use App\Service\PersistenceService;
use App\Service\Product\Validation\ProductValidationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductCreationService
{

    public function __construct (
        private readonly PersistenceService                   $persistenceService,
        private readonly HttpResponseInterface                $httpResponse,
        private readonly AuthService                          $authService,
        private readonly ProductValidationService             $productValidationService,
        private readonly ProductCategoryRepository            $categoryRepository,
        private readonly FileUploaderService                  $fileUploader,
        private readonly EnvironmentVariablesServiceInterface $environmentVariablesService,
        private readonly ActivityLogService                   $activityLogService,
    )
    {
    }

    public function init(Request $request): JsonResponse
    {
        try {
            return $this->processProductCreation($request);
        } catch (AuthenticationException) {
            return $this->httpResponse->unauthorizedResponse();
        } catch (UnauthorizedException) {
            return $this->httpResponse->forbiddenResponse();
        } catch (InvalidProductInfoException $e) {
            return $this->httpResponse->validationErrorResponse($e->getErrors());
        } catch (InvalidCategoryException) {
            return $this->httpResponse->validationErrorResponse(['category' => 'Please make sure that the category actually exists.']);
        } catch (FileNotFoundException) {
            return $this->httpResponse->validationErrorResponse(['file' => 'Please make sure to upload the file']);
        } catch (InvalidFileException) {
            return $this->httpResponse->validationErrorResponse(['file' => 'Invalid file type/extension.']);
        }
    }

    /**
     * @throws InvalidProductInfoException
     * @throws UnauthorizedException
     * @throws InvalidCategoryException
     * @throws AuthenticationException
     * @throws FileNotFoundException
     * @throws InvalidFileException
     */
    public function processProductCreation(Request $request): JsonResponse
    {
        $this->authService->authenticateAndAuthorize('ROLE_ADMIN');
        $this->authService->checkAuthorization('ROLE_PRODUCT_CREATE');

        [$categoryId, $category]    = $this->validate($request);
        [$fileUploadData, $product] = $this->storeProduct($request, $category);

        return $this->getResponse($product, $categoryId, $fileUploadData);
    }

    public function createProduct(Request $request, ProductCategory $category, FileUploadData $fileUploadData): Product
    {
        $product = new Product();

        $product->setName($request->get('name'));
        $product->setDescription($request->get('description'));
        $product->setPrice($request->get('price'));
        $product->setCategory($category);
        $product->setIsActive((bool) $request->get('isActive'));

        $product->setS3FileName($fileUploadData->s3FileName);
        $product->setOriginalFileName($fileUploadData->originalFileName);

        // saves the product to the database
        $this->persistenceService->sync($product);

        return $product;
    }

    /**
     * @throws InvalidCategoryException
     */
    public function validateCategory(Request $request): array
    {
        // Query category
        $categoryId = $request->get('category');
        $category   = $this->categoryRepository->findOneBy(['id' => $categoryId]);

        // validate the category
        if (!$category) {
            throw new InvalidCategoryException();
        }

        return [$categoryId, $category];
    }

    private function getResponse(Product $product, string $categoryId, FileUploadData $fileUploadData): JsonResponse
    {
        return new JsonResponse([
            'id'           => $product->getId(),
            'name'         => $product->getName(),
            'description'  => $product->getDescription(),
            'price'        => $product->getPrice(),
            'category'     => '/api/categories/' . $categoryId,
            'isActive'     => $product->isActive(),
            'productImage' => "https://{$this->environmentVariablesService->get(EnvVars::BUCKET_NAME)}.s3.{$this->environmentVariablesService->get(EnvVars::REGION)}.amazonaws.com/{$fileUploadData->s3FileName}"
        ], Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return array
     * @throws InvalidCategoryException
     * @throws InvalidProductInfoException
     */
    public function validate(Request $request): array
    {
        $this->productValidationService->validate($request);
        [$categoryId, $category] = $this->validateCategory($request);

        return [$categoryId, $category];
    }

    /**
     * @param Request $request
     * @param mixed $category
     * @return array
     * @throws FileNotFoundException
     * @throws InvalidFileException
     */
    public function storeProduct(Request $request, mixed $category): array
    {
        $fileUploadData = $this->fileUploader->initUpload($request);
        $product        = $this->createProduct($request, $category, $fileUploadData);

        // log
        $this->activityLogService->storeLog(ActivityLog::CREATE_PRODUCT, $product);

        return [$fileUploadData, $product];
    }

}