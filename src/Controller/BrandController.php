<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Form\BrandType;
use App\Repository\BrandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use FOS\RestBundle\Controller\Annotations as Rest;


/**
 * @Route("/brand")
 */
class BrandController extends AbstractController
{
    /**
     * @var BrandRepository
     */
    private $brandRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    private $security;


    public function __construct(BrandRepository $brandRepository,Security $security){
        $this->brandRepository = $brandRepository;
        $this->security = $security;
    }

    /**
     * @Route("/", name="brand_index", methods={"GET"})
     * @param BrandRepository $brandRepository
     * @return Response
     */
    public function index(BrandRepository $brandRepository): Response
    {
        $user = $this->security->getUser();
        return $this->render('brand/index.html.twig', [
            'brands' => $brandRepository->findByCompany($user->getCompany()),
        ]);
    }

    /**
     * @Route("/new", name="brand_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $brand = new Brand();


        $user = $this->security->getUser();
        $brand->setCompany($user->getCompany());

        $form = $this->createForm(BrandType::class, $brand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager = $this->getDoctrine()->getManager();

            /**@var UploadedFile $file */
            $file = $request->files->get('brand')['attachment'];
            if($file){
                $filename = md5(uniqid()). '.' . $file->guessClientExtension();
                $file->move(
                    $this->getParameter('uploads_dir'),
                    $filename);
                $brand->setImage($filename);
            }

            $entityManager->persist($brand);
            $entityManager->flush();

            return $this->redirectToRoute('brand_index');
        }

        return $this->render('brand/new.html.twig', [
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/{id}", name="brand_show", methods={"GET"})
     * @param BrandRepository $brandRepository
     * @param $id
     * @return Response
     * @throws NonUniqueResultException
     */
    public function show(BrandRepository $brandRepository,$id): Response
    {
        $user = $this->security->getUser();
        $brand = $brandRepository->findOneByCompanyID($user->getCompany(), $id);

        return $this->render('brand/show.html.twig', [
            'brand' => $brand,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="brand_edit", methods={"GET","POST"})
     * @param BrandRepository $brandRepository
     * @param $id
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function edit(BrandRepository $brandRepository,$id,Request $request): Response
    {
        $user = $this->security->getUser();
        $company = $user->getCompany();
        $entityManager = $this->getDoctrine()->getManager();
        $brand = $brandRepository->findOneByCompanyID($user->getCompany(), $id);

        $originalStore = new ArrayCollection();

        foreach ($brand->getStore() as $store) {
            $originalStore->add($store);
        }

        $form = $this->createForm(BrandType::class, $brand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // remove the relationship between the Treatment and the Equipment
            foreach ($originalStore as $store) {
                if (false === $brand->getStore()->contains($store)) {
                    $entityManager->remove($store);
                    $entityManager->persist($store);
                }
            }
            $entityManager->persist($brand);
            $entityManager->flush();

            return $this->redirectToRoute('brand_index');
        }

        return $this->render('brand/edit.html.twig', [
            'brand' => $brand,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="brand_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Brand $brand): Response
    {
        if ($this->isCsrfTokenValid('delete'.$brand->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($brand);
            $entityManager->flush();
        }

        return $this->redirectToRoute('brand_index');
    }
}
