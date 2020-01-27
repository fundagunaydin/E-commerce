<?php

namespace App\Controller;

use App\Entity\Admin\Messages;
use App\Entity\Emlak;
use App\Entity\Setting;
use App\Form\Admin\MessagesType;
use App\Repository\Admin\CommentRepository;
use App\Repository\EmlakRepository;
use App\Repository\ImageRepository;
use App\Repository\SettingRepository;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Smtp\GmailTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SettingRepository  $settingRepository, EmlakRepository $emlakRepository)
    {
        $setting=$settingRepository->findAll();
        $slider=$emlakRepository->findAll();
        $emlaks=$emlakRepository->findBy([],['id'=>'ASC'],3);
        $newemlaks=$emlakRepository->findBy([],['id'=>'DESC'],3);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting'=> $setting,
            'slider'=>$slider,
            'emlaks'=>$emlaks,
            'newemlaks'=>$newemlaks,

        ]);
    }

    /**
     * @Route("/emlak/{id}", name="emlak_show", methods={"GET"})
     */
    public function show(Emlak $emlak,$id,ImageRepository $imageRepository,CommentRepository $commentRepository,EmlakRepository $emlakRepository): Response
    {
        $images=$imageRepository->findBy(['emlak'=>$id]);
        $image=$emlakRepository->getAllImages($id);  //homede sadece status true olanları göstersin

        $comments=$commentRepository->getAllComments();  //burda commentleri SQL sorgumdan getirdim


        return $this->render('home/emlakshow.html.twig', [
            'emlak' => $emlak,
            'images' => $images,
            'image'=>$image,
            'comments' => $comments,    //sayfaya gönderiyor
        ]);
    }
    /**
     * @Route("/about", name="home_about")
     */
    public function about(SettingRepository  $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/aboutus.html.twig', [
            'setting'=> $setting,
        ]);
    }
    /**
     * @Route("/bizkimiz", name="home_bizkimiz")
     */
    public function bizkimiz(SettingRepository  $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/bizkimiz.html.twig', [
            'setting'=> $setting,
        ]);
    }
    /**
     * @Route("/misyonvevizyon", name="home_misyonvevizyon")
     */
    public function misyonvevizyon(SettingRepository  $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/misyonvevizyon.html.twig', [
            'setting'=> $setting,
        ]);
    }
    /**
     * @Route("/mesaj", name="home_mesaj")
     */
    public function mesaj(SettingRepository  $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/mesaj.html.twig', [
            'setting'=> $setting,
        ]);
    }


    /**
     * @Route("/contact", name="home_contact", methods={"GET","POST"})
     */
    public function contact(SettingRepository  $settingRepository,Request $request): Response
    {
        $setting=$settingRepository->findAll(); // veriyi çekiyor findall ile
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken=$request->request->get('token');

        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('form-messeage', $submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();
                $message->setStatus('New');
                $message->setIp($_SERVER['REMOTE_ADDR']);
                $entityManager->persist($message);
                $entityManager->flush();
                $this->addFlash('success','Mesajınız başarı ile gönderilmiştir');

                //*************************SEND EMAİL*******************************
                $email=(new Email())

                    ->from($setting[0]->getSmtpemail())

                    ->to($form['email']->getData())

                    ->subject('Günaydın İnşaat ')

                    ->html("Dear ".$form['name']->getData() ."<br>
                        <p> We will evaluate your request and contact you as soon as possible</p>
                        Thank you  for your message<br>
                        ==============================================================
                        <br>".$setting[0]->getCompany()."  <br>
                        Address: ".$setting[0]->getAddress()."<br>
                        Phone: ".$setting[0]->getPhone()."<br>"
                    );

                $transport=new GmailTransport($setting[0]->getSmtpemail(),$setting[0]->getSmtppassword()); //settingdeki smtpemail ve smtppassword b,lg,ler,m,
                $mailer=new Mailer($transport);
                $mailer->send($email);

                //**********************SEND EMAİL************************************

                return $this->redirectToRoute('home_contact');
            }
        }


        $setting=$settingRepository->findAll();
        return $this->render('home/contact.html.twig', [
            'setting'=> $setting,
            'form' => $form->createView(),
        ]);
    }
}


