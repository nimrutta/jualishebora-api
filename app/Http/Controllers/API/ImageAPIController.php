<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateImageAPIRequest;
use App\Http\Requests\API\UpdateImageAPIRequest;
use App\Models\Image;
use App\Repositories\ImageRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ImageController
 * @package App\Http\Controllers\API
 */
class ImageAPIController extends AppBaseController
{
    /** @var  ImageRepository */
    private $imageRepository;

    public function __construct(ImageRepository $imageRepo)
    {
        $this->imageRepository = $imageRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/images",
     *      summary="Get a listing of the Images.",
     *      tags={"Image"},
     *      description="Get all Images",
     *      produces={"application/json"},
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/Image")
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        $this->imageRepository->pushCriteria(new RequestCriteria($request));
        $this->imageRepository->pushCriteria(new LimitOffsetCriteria($request));
        $images = $this->imageRepository->all();

        return $this->sendResponse($images->toArray(), 'Images retrieved successfully');
    }

    /**
     * @param CreateImageAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/images",
     *      summary="Store a newly created Image in storage",
     *      tags={"Image"},
     *      description="Store Image",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Image that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Image")
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Image"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(Request $request)
    {

        $url = URL::to('/').'/storage/images/'.$request->image->hashName();
        $request->image->store('images','public');
        $image = new Image();
        $image->teacher_id = 1;
        $image->image_url = $url;
        $image->save();
        return $this->sendResponse($image->toArray(), 'Image saved successfully');

    }


    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/images/{id}",
     *      summary="Display the specified Image",
     *      tags={"Image"},
     *      description="Get Image",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Image",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Image"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show($id)
    {
        /** @var Image $image */
        $image = $this->imageRepository->findWithoutFail($id);

        if (empty($image)) {
            return $this->sendError('Image not found');
        }

        return $this->sendResponse($image->toArray(), 'Image retrieved successfully');
    }

    /**
     * @param int $id
     * @param UpdateImageAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/images/{id}",
     *      summary="Update the specified Image in storage",
     *      tags={"Image"},
     *      description="Update Image",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Image",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Image that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Image")
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/Image"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateImageAPIRequest $request)
    {
        $input = $request->all();

        /** @var Image $image */
        $image = $this->imageRepository->findWithoutFail($id);

        if (empty($image)) {
            return $this->sendError('Image not found');
        }

        $image = $this->imageRepository->update($input, $id);

        return $this->sendResponse($image->toArray(), 'Image updated successfully');
    }


    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/images/{id}",
     *      summary="Remove the specified Image from storage",
     *      tags={"Image"},
     *      description="Delete Image",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Image",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function destroy($id)
    {
        /** @var Image $image */
        $image = $this->imageRepository->findWithoutFail($id);

        if (empty($image)) {
            return $this->sendError('Image not found');
        }

        $image->delete();

        return $this->sendResponse($id, 'Image deleted successfully');
    }
}
