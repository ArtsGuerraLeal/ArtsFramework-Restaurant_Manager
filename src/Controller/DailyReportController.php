<?php

namespace App\Controller;

use App\Entity\DailyReport;
use App\Form\ReportPatientType;
use App\Repository\CompanyRepository;
use App\Repository\DailyReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class DailyReportController extends AbstractFOSRestController
{


    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    private $entityManager;
    /**
     * @var DailyReportRepository
     */
    private $dailyReportRepository;
    private $security;

    public function __construct(Security $security, DailyReportRepository $dailyReportRepository, CompanyRepository $companyRepository, EntityManagerInterface $entityManager)
    {
     $this->companyRepository = $companyRepository;
     $this->entityManager = $entityManager;
     $this->dailyReportRepository = $dailyReportRepository;
     $this->security = $security;
    }

    /**
     * @Route("/dailyreport", name="daily_report")
     */
    public function index(Request $request): Response
    {
        $user = $this->security->getUser();
        $reportData = null;
        $entityManager = $this->getDoctrine()->getManager();


        $form = $this->createForm(ReportPatientType::class);
        $form->handleRequest($request);

        $formData = $form->getData();

        $startTime = new \DateTime();
        $startTime->format('y-m-d H:i:s');
        $startTime->setTime(0,0,0);

        $endTime = new \DateTime();
        $endTime->format('y-m-d H:i:s');
        $endTime->setTime(23,59,59);



        $reportData = $this->dailyReportRepository->createQueryBuilder('p')
            ->Where('p.dateCreated >= :firstDate AND p.dateCreated <= :secondDate')
       //     ->Where('p.dateCreated >= :firstDate AND p.dateCreated <= :secondDate')
            ->andWhere('p.company = :companyId')
            ->setParameter('firstDate',$startTime)
            ->setParameter('secondDate',$endTime)
            ->setParameter('companyId',$user->getCompany()->getId())
            ->getQuery()
            ->getResult()
        ;



        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('clear')->isClicked()){
                return $this->render('report/_patient.html.twig', [
                    'form' => $form->createView(),
                    'reports' => null
                ]);
            }
            $reportData = $this->dailyReportRepository->createQueryBuilder('p')
                ->where('p.date_created >= :firstDate AND p.date_created <= :secondDate')
                ->andWhere('p.company = :companyId')
                ->setParameter('firstDate',$formData['startdate'])
                ->setParameter('secondDate',$formData['enddate'])
                ->setParameter('companyId',$user->getCompany()->getId())
                ->getQuery()
                ->getResult()
            ;

            return $this->render('daily_report/_dailyreport.html.twig', [
                'form' => $form->createView(),
                'reports' => $reportData
            ]);
        }

        return $this->render('daily_report/_dailyreport.html.twig', [
            'form' => $form->createView(),
            'reports' => $reportData
        ]);
    }

    /**
     * @Route("/dailyreport/{id}", name="daily_report_show")
     * @throws NonUniqueResultException
     */
    public function show(DailyReportRepository $dailyReportRepository, $id): Response
    {
        $user = $this->security->getUser();
        $dailyReport = $dailyReportRepository->findOneByCompanyID($user->getCompany(), $id);

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($dailyReport->getData(), 'json');

        $customData = $serializer->decode($jsonContent,'json');

        $dataArray = json_decode($customData[0],true);



        return $this->render('daily_report/show.html.twig', [
            'report' => $dailyReport,
            'data' => $dataArray

        ]);
    }





    /**
     * @Rest\Route("/dailyreport")
     * @Rest\RequestParam(name="companyId", description="Company Id", nullable=false)
     * @Rest\RequestParam(name="data", description="Last Name", nullable=false)
     * @Rest\RequestParam(name="dateCreated", description="DateCreated", nullable=false)
     * @param ParamFetcher $paramFetcher
     * @return View
     */
    public function postDailyReportAction(ParamFetcher $paramFetcher){
        $companyId = $paramFetcher->get('companyId');
        $company = $this->companyRepository->findOneBy(['id' => $companyId]);
        $data = $paramFetcher->get('data');
        $dateCreated = $paramFetcher->get('dateCreated');

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($data, 'json');


        if($companyId){
            $dailyReport = new DailyReport();

            $dailyReport->setCompany($company);
            $dailyReport->setData(array($jsonContent));
            $dailyReport->setDateCreated(\DateTime::createFromFormat('Y-m-d H:i:s', $dateCreated));


            $this->entityManager->persist($dailyReport);
            $this->entityManager->flush();

            return $this->view($dailyReport,Response::HTTP_CREATED);

        }
        return $this->view(['firstName' => 'this cannot be null'],Response::HTTP_BAD_REQUEST);
    }
}
