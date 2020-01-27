<?php

namespace App\Controller;

use App\Entity\Emlak;
use App\Form\Emlak1Type;
use App\Repository\CategoryRepository;
use App\Repository\EmlakRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/emlak")
 */
class EmlakController extends AbstractController
{
    /**
     * @Route("/", name="user_emlak_index", methods={"GET"})
     */
    public function index(EmlakRepository $emlakRepository): Response
    {
        $user = $this->getUser(); // login olan user bilgisine erişmek için
        return $this->render('emlak/index.html.twig', [
            'emlaks' => $emlakRepository->findBy(['userid'=>$user->getid()]), //kullanıcı sadece kendi eklediği bilgileri görecek
        ]);
    }

    /**
     * @Route("/new", name="user_emlak_new", methods={"GET","POST"})
     */
    public function new(Request $request,CategoryRepository $categoryRepository): Response
    {
        $emlak = new Emlak();
        $form = $this->createForm(Emlak1Type::class, $emlak);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            //********************file upload*******************************************
            $file=$form['image']->getData();
            if($file){
                $fileName=$this->generateUniqueFileName() . '.' . $file->guessExtension();

                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );

                }catch(FileException $e){
                    //------------
                }

                $emlak->setImage($fileName);
            }
            //*****************file upload*******************************************

            $user = $this->getUser();     //login olanın bilgilerini getiriyor.
            $emlak->setUserid($user->getId());
            $emlak->setStatus("New");  //kullanıcı emlak statusu değiştiremeyecek,izin vermiyoruz.

            $entityManager->persist($emlak);
            $entityManager->flush();

            return $this->redirectToRoute('user_emlak_index');
        }

        return $this->render('emlak/new.html.twig', [
            'emlak' => $emlak,
            'form' => $form->createView(),
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_emlak_show", methods={"GET"})
     */
    public function show(Emlak $emlak): Response
    {
        return $this->render('emlak/show.html.twig', [
            'emlak' => $emlak,
        ]);
    }
    /**
     * @Route("/{id}/detail", name="user_detail", methods={"GET", "POST"})
     */
    public function detail(Emlak $emlak, $id, Request $request): Response
    {
        if ($request->isMethod('post')) {
            $emlak->setDetail($request->request->get('detail'));
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('user_detail', ['id' => $id]);
        }

        return $this->render('admin/emlak/detail.html.twig', [
            'emlak' => $emlak,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="user_emlak_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Emlak $emlak,CategoryRepository $categoryRepository): Response
    {
        $form = $this->createForm(Emlak1Type::class, $emlak);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//*********************************************************************************

            $file=$form['image']->getData();
            if($file){
                $fileName=$this->generateUniqueFileName() . '.' . $file->guessExtension();

                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                }catch(FileException $e){
                    //------------
                }
                $emlak->setImage($fileName);
            }
//**************************************************************************
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_emlak_index');
        }

        return $this->render('emlak/edit.html.twig', [
            'emlak' => $emlak,
            'form' => $form->createView(),
            'categories' => $categoryRepository->findAll(),
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
     * @Route("/{id}", name="user_emlak_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Emlak $emlak): Response
    {

        if ($this->isCsrfTokenValid('delete'.$emlak->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($emlak);
            $entityManager->flush();

        }

        return $this->redirectToRoute('user_emlak_index');
    }
}
