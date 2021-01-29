<?php

namespace App\Modules;

use App\User;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;

class ActivationService
{

    protected $mailer;

    protected $activationRepo;

    protected $resendAfter = 24;

    public function __construct(Mailer $mailer, ActivationRepository $activationRepo)
    {
        $this->mailer = $mailer;
        $this->activationRepo = $activationRepo;
    }

    public function sendActivationMail($user)
    {
        if ($user[0]['activated'] || !$this->shouldSend($user)) {
            return response()->json(['status'=>'actived']);
        }

        $token = $this->activationRepo->createActivation($user);

        $link = route('user.activate', $token);

        $this->mailer->send('email.activation', ['link'=>$link], function (Message $m) use ($user) {
            $m->to($user[0]['email'])->subject('[LMS] Activation mail');
        });



        return 'ok';
    }

    public function activateUser($token)
    {
        $activation = $this->activationRepo->getActivationByToken($token);

        if ($activation === null) {
            return null;
        }

        $user = User::find($activation->user_id);

        $user->activated = true;

        $user->save();

        $this->activationRepo->deleteActivation($token);

        return $user;

    }

    private function shouldSend($user)
    {
        $activation = $this->activationRepo->getActivation($user);
        return $activation === null || strtotime($activation->created_at) + 60 * 60 * $this->resendAfter < time();
    }

}