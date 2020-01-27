<?php

namespace App\Controller\Admin;

use App\Entity\Emlak;
use App\Form\EmlakType;
use App\Repository\EmlakRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/emlak")
 */
class EmlakController extends AbstractController
{
    /**
     * @Route("/", name="admin_emlak_index", methods={"GET"})
     */
    public function index(EmlakRepository $emlakRepository): Response
    {
        $emlaks = $emlakRepository->getAllEmlaks();
        return $this->render('admin/emlak/index.html.twig',
            [
                'emlaks' => $emlaks
            ]
        );
    }

    /**
     * @Route("/new", name="admin_emlak_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $emlak = new Emlak();
        $userid = $this->getUser()->getId();  //login olan userid getirdim
        $form = $this->createForm(EmlakType::class, $emlak);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {  //forma tıklandığında gir
            $entityManager = $this->getDoctrine()->getManager();
            //********************file upload*******************************************
            $file = $form['image']->getData();
            if ($file) {
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );

                } catch (FileException $e) {
                    //------------
                }
                $emlak->setImage($fileName);
            }
            //*****************file upload*******************************************
            $emlak->setUserid($userid); //login olan useridiyi databaseye ekledim
            $entityManager->persist($emlak);
            $entityManager->flush();

            return $this->redirectToRoute('admin_emlak_index');
        }

        return $this->render('admin/emlak/new.html.twig', [    //forma tıklanmadıgında gir
            'emlak' => $emlak,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_emlak_show", methods={"GET"})
     */
    public function show(Emlak $emlak): Response
    {
        return $this->render('admin/emlak/show.html.twig', [
            'emlak' => $emlak,
        ]);
    }

    /**
     * @Route("/{id}/detail", name="admin_detail", methods={"GET", "POST"})
     */
    public function detail(Emlak $emlak, $id, Request $request): Response
    {
        if ($request->isMethod('post')) {
            $emlak->setDetail($request->request->get('detail'));
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_detail', ['id' => $id]);
        }

        return $this->render('admin/emlak/detail.html.twig', [
            'emlak' => $emlak,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="admin_emlak_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Emlak $emlak): Response
    {
        $form = $this->createForm(EmlakType::class, $emlak);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//*********************************************************************************

            $file = $form['image']->getData();
            if ($file) {
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    //------------
                }
                $emlak->setImage($fileName);
            }
//**************************************************************************

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_emlak_index');
        }

        return $this->render('admin/emlak/edit.html.twig', [
            'emlak' => $emlak,
            'form' => $form->createView(),


        ]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }


    /**
     * @Route("/{id}", name="admin_emlak_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Emlak $emlak): Response
    {

        if ($this->isCsrfTokenValid('delete' . $emlak->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($emlak);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_emlak_index');
    }
}
