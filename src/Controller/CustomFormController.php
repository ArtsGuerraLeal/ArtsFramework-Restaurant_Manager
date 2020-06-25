<?php

namespace App\Controller;

use App\Entity\CustomForm;
use App\Entity\Equipment;
use App\Form\CustomFormType;
use App\Repository\CustomFormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations as Rest;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/customform")
 */
class CustomFormController extends AbstractFOSRestController
{
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var CustomFormRepository
     */
    private $customFormRepository;

    public function __construct(CustomFormRepository $customFormRepository, EntityManagerInterface $entityManager,Security $security){
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->customFormRepository = $customFormRepository;

    }

    /**
     * @Route("/", name="custom_form_index", methods={"GET"})
     * @param CustomFormRepository $customFormRepository
     * @return Response
     */
    public function index(CustomFormRepository $customFormRepository): Response
    {

        $customForm = $customFormRepository->findOneByCompany(1);

        $user = $this->security->getUser();
        return $this->render('custom_form/index.html.twig', [
            'custom_forms' => $customFormRepository->findByCompany($user->getCompany()),
        ]);
    }

    /**
     * @Route("/new", name="custom_form_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {

        $customForm = new CustomForm();

        $user = $this->security->getUser();
        $customForm->setCompany($user->getCompany());

        $form = $this->createForm(CustomFormType::class, $customForm);
        $form->handleRequest($request);
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($form->get('customdata')->getData(), 'json', ['ignored_attributes' => ['company','customForm','id']]);;

        $customData =  $serializer->decode($jsonContent,'json');

        if ($form->isSubmitted() && $form->isValid()) {

            $index = 0;

            foreach ($customData as $cd) {
                $cd['name'] =  preg_replace('/\s+/', '_', $cd['name']);
                $customData[$index]['name'] = $cd['name'];
                $index++;
                }

            $customForm->setFields($customData);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($customForm);
            $entityManager->flush();

            return $this->redirectToRoute('custom_form_index');
        }

        return $this->render('custom_form/new.html.twig', [
            'custom_form' => $customForm,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="custom_form_show", methods={"GET"})
     * @param CustomFormRepository $customFormRepository
     * @param $id
     * @return Response
     * @throws NonUniqueResultException
     */
    public function show(CustomFormRepository $customFormRepository, $id): Response
    {
        $user = $this->security->getUser();
        $customForm = $customFormRepository->findOneByCompanyID($user->getCompany(), $id);
        return $this->render('custom_form/show.html.twig', [
            'custom_form' => $customForm,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="custom_form_edit", methods={"GET","POST"})
     * @param Request $request
     * @param CustomForm $customForm
     * @return Response
     */
    public function edit(Request $request, CustomForm $customForm): Response
    {
        $user = $this->security->getUser();
        $company = $user->getCompany();
        $entityManager = $this->getDoctrine()->getManager();

        $dataForm = $this->createFormBuilder();

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($customForm->getFields(), 'json', ['ignored_attributes' => ['company','customForm','id']]);;
        $customData =  $serializer->decode($jsonContent,'json');

        foreach ($customData as $cd){

            if($cd["type"] == "Int"){
                $dataForm->add($cd['name'], NumberType::class);

            }elseif($cd["type"] == "String"){
                $dataForm->add($cd['name'], TextType::class);

            }elseif($cd["type"] == "Boolean") {
                $dataForm->add($cd['name'], CheckboxType::class);

            }

        }

        $dataForm ->add('save', SubmitType::class, ['label' => 'Create Medical']);

        $form = $dataForm->getForm();

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $form->handleRequest($request);
        $jsonContent = $serializer->serialize($form->getData(), 'json');


        if ($form->isSubmitted() && $form->isValid()) {
            try {
                var_dump($jsonContent);
                $customForm->setFields(array($jsonContent));
                $entityManager->persist($customForm);
                $entityManager->flush();
                // $fs = new Filesystem();
                // $fs->dumpFile('json/'.$user->getCompany()->getName().'/'.$patient->getId() .'.json', $jsonContent);
            }
            catch(IOException $e) {
            }

            return $this->redirectToRoute('custom_form_index');
        }

        return $this->render('custom_form/edit.html.twig', [
            'custom_form' => $customForm,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/{id}", name="custom_form_delete", methods={"DELETE"})
     * @param Request $request
     * @param CustomForm $customForm
     * @return Response
     */
    public function delete(Request $request, CustomForm $customForm): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customForm->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($customForm);
            $entityManager->flush();
        }

        return $this->redirectToRoute('custom_form_index');
    }

    /**
     * @Rest\Route("/customform/{companyId}/{id}")
     * @param CustomFormRepository $customFormRepository
     * @param $companyId
     * @param $id
     * @return \FOS\RestBundle\View\View
     * @throws NonUniqueResultException
     */
    public function getCustomFormActions(CustomFormRepository $customFormRepository, $companyId,$id){

            $customForm = $customFormRepository->findOneByCompanyID($companyId,$id);

        return $this->view($customForm,Response::HTTP_OK);
    }


    /**
     * @Rest\RequestParam(name="name", description="Line 1", nullable=false)
     * @Rest\RequestParam(name="cost", description="Line 2", nullable=true)
     * @Rest\RequestParam(name="purchasedOn", description="Postal Code", nullable=true)
     * @Rest\RequestParam(name="lastUsedOn", description="City", nullable=true)
     * @param ParamFetcher $paramFetcher
     * @return View
     */
    public function postCustomFormAction(ParamFetcher $paramFetcher){
        $name = $paramFetcher->get('name');
        $cost = $paramFetcher->get('cost');
        $purchased_on = $paramFetcher->get('purchasedOn');
        $last_used_on = $paramFetcher->get('lastUsedOn');

        if($name){
            $equipment = new Equipment();
            $equipment->setName($name);
            $equipment->setCost($cost);
            $equipment->setPurchasedOn($purchased_on);
            $equipment->setLastUsedOn($last_used_on);
            $this->entityManager->persist($equipment);
            $this->entityManager->flush();
            return $this->view($equipment,Response::HTTP_CREATED);

        }


        return $this->view(['name' => 'this cannot be null'],Response::HTTP_BAD_REQUEST);
    }


}
