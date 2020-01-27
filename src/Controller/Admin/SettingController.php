<?php

namespace App\Controller\Admin;

use App\Entity\Setting;
use App\Form\SettingType;
use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/setting")
 */
class SettingController extends AbstractController
{
    /**
     * @Route("/", name="setting_index", methods={"GET"})
     */
    public function index(SettingRepository $settingRepository): Response
    {
        return $this->render('admin/setting/index.html.twig', [
            'settings' => $settingRepository->findBy([],['id'=>'DESC']), //En Son gönderilenler en üstte
        ]);
    }

    /**
     * @Route("/new", name="setting_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $setting = new Setting();
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($setting);
            $entityManager->flush();

            return $this->redirectToRoute('setting_index');
        }

        return $this->render('admin/setting/new.html.twig', [
            'setting' => $setting,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="setting_show", methods={"GET"})
     */
    public function show(Setting $setting): Response
    {
        return $this->render('admin/setting/show.html.twig', [
            'setting' => $setting,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_setting_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Setting $setting): Response
    {
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('setting_index');
        }

        return $this->render('admin/setting/edit.html.twig', [
            'setting' => $setting,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/editabout", name="admin_setting_editabout", methods={"GET","POST"})
     */
    public function editabout(Request $request, $id,Setting $setting): Response
    {
        
        if ($request->isMethod('post')) {    
            $setting->setAboutUs($request->request->get('aboutus'));
            $setting->setEmail($request->request->get('email'));
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_setting_editabout',['id'=>$id]);
        }

        return $this->render('admin/setting/editabout.html.twig', [
            'setting' => $setting,
        ]);
    }

    /**
     * @Route("/{id}/editcontact", name="admin_setting_editcontact", methods={"GET","POST"})
     */
    public function editcontact(Request $request, $id,Setting $setting): Response
    {
      

        if ($request->isMethod('post')) {    
            $setting->setContact($request->request->get('contact'));
            $setting->setEmail($request->request->get('email'));
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_setting_editcontact',['id'=>$id]);
        }

        

        return $this->render('admin/setting/editcontact.html.twig', [
            'setting' => $setting,
        ]);
    }

    /**
     * @Route("/{id}/editreference", name="admin_setting_editreference", methods={"GET","POST"})
     */
    public function editreference(Request $request, $id,Setting $setting): Response
    {
    
        if ($request->isMethod('post')) {    
            $setting->setReference($request->request->get('reference'));
            $setting->setEmail($request->request->get('email'));
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_setting_editreference',['id'=>$id]);
        }

        

        return $this->render('admin/setting/editreference.html.twig', [
            'setting' => $setting,
        ]);
    }




    /**
     * @Route("/{id}", name="setting_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Setting $setting): Response
    {
        if ($this->isCsrfTokenValid('delete'.$setting->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($setting);
            $entityManager->flush();
        }

        return $this->redirectToRoute('setting_index');
    }
}
