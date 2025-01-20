<?php

declare(strict_types = 1);

namespace App\Service\Product\Patch;

use App\Contracts\AuthInterface;
use App\Contracts\DateAndTimeInterface;
use App\Contracts\File\S3\FileUploaderInterface;
use App\Contracts\PersistenceServiceInterface;
use App\Entity\Product;
use App\Enum\DateTime;
use App\Exception\FileNotFoundException;
use App\Exception\FileTooLargeException;
use App\Exception\InvalidFileException;
use App\Exception\InventoryException\ProductNotFoundException;
use App\Factories\ProductResponseFactory;
use App\Http\ProductImageUpdateExceptionHandler;
use App\Repository\ProductRepository;
use App\Service\Product\Patch\RequestMapper\ProductImageUpdateRequestMapper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class ProductImageUpdateService
{

    /**
     * Roles declaration for Authentication/Authorization operation
     *
     * Roles required for authentication and authorization check for this image update/patch
     */
    private const string ROLE_ADMIN        = 'ROLE_ADMIN';
    private const string ROLE_PRODUCT_EDIT = 'ROLE_PRODUCT_EDIT';

    /**
     * Request data, i.e. data sent during request
     *
     * Data sent during request for the image update
     */
    private const string FILE_KEY       = 'file';
    private const string PRODUCT_ID_KEY = 'id';

    public function __construct (
        private readonly DateAndTimeInterface $dateAndTime,
        private readonly PersistenceServiceInterface $persistenceService,
        private readonly ProductRepository $repository,
        private readonly AuthInterface $auth,
        private readonly FileUploaderInterface $fileUploader,
        private readonly ProductResponseFactory $productResponseFactory,
        private readonly ProductImageUpdateExceptionHandler $exceptionHandler,
        private readonly ProductImageUpdateRequestMapper $requestMapper,
    )
    {
    }

    public function init(Request $request): JsonResponse
    {
        try {
            return $this->process($request);
        } catch (Throwable $e) {
            return $this->exceptionHandler->handle($e);
        }
    }

    /**
     * @throws InvalidFileException
     * @throws FileTooLargeException
     * @throws FileNotFoundException
     * @throws ProductNotFoundException
     */
    public function process(Request $request): JsonResponse
    {
        // check authentication/authorization
        $this->auth->checkAuthentication();
        $this->auth->checkRolesForAuthorization(roles: [self::ROLE_ADMIN, self::ROLE_PRODUCT_EDIT]);

        // map the request data into DTO
        $productRequestData = $this->requestMapper->map($request);

        $product    = $this->findProductById($productRequestData->productId);
        $s3FileName = $this->getS3FileName($product);

        // update file
        $this->update($s3FileName, $productRequestData->uploadedFile, $product);

        // create product response
        return $this->productResponseFactory->create($product, $productRequestData->productId, $s3FileName);
    }

    public function update(string $s3FileName, UploadedFile $uploadedFile, Product $product): void
    {
        // update the file to S3
        $this->fileUploader->uploadToS3($s3FileName, $uploadedFile->getRealPath(), $uploadedFile->getMimeType());

        // update file info
        $this->updateProductInfo($product, $uploadedFile);

        $this->persistenceService->flush();
    }

    public function getS3FileName(Product $product): string
    {
        // S3 file name ( from the database ) so that we can replace it in the s3 bucket
        return $product->getS3FileName();
    }

    public function updateProductInfo(Product $product, UploadedFile $uploadedFile): void
    {
        $this->setOriginalFileName($product, $uploadedFile->getClientOriginalName());
        $this->setUpdatedAt($product);
    }

    private function findProductById(string $productId): ?Product
    {
        $product = $this->repository->find($productId);

        if (!$product) {
            throw new ProductNotFoundException();
        }

        return $product;
    }

    private function setOriginalFileName(Product $product, string $originalFileName): void
    {
        $product->setOriginalFileName($originalFileName);
    }

    private function setUpdatedAt(Product $product): void
    {
        $product->setUpdatedAt(new \DateTimeImmutable(DateTime::CURRENT->value, $this->dateAndTime->getTimeZone()));
    }
}