<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {

    return view('welcome');
});


Route::get('/workflow', function (\App\Models\TelegramRequest $request) {
    /*$calendar = new \App\UseCase\Telegram\Calendar();
    $calendarStructure = $calendar->makeCalendar();


    $sender->editMessage(216714025,281, 'КЕК');*/

    $entity = $request->findNotFinishedByUserId(216714025);
    $entity->setLastMessage('тест');
    $entity->save();
    dump($message);
    dd($entity);


    die;

    /*$sender->sendMessage(216714025, 'ну привет!');
    dd('stop');*/
    /*$entity = \App\Models\TelegramRequest::create([
        'city_id' => 4182,
        'check_in' => new DateTime(),
        'check_out' => (new DateTime())->add(new DateInterval('P1W2D')),
        'adults' => 2,
    ]);
    die;*/
    $entity = \App\Models\TelegramRequest::first();
    /** @var \Symfony\Component\Workflow\Workflow $workflow */
    $workflow = \ZeroDaHero\LaravelWorkflow\Facades\WorkflowFacade::get($entity);
    foreach($workflow->getDefinition()->getTransitions() as $transition) {
        dump();
    }

    dd("###");

//    $res = $workflow->getEnabledTransitions($entity);


    foreach($workflow->getDefinition()->getTransitions() as $transition) {
        if(
            in_array($entity->status, $transition->getFroms())
            && !$workflow->can($entity, $transition->getName())
            && $transitionBlockerList = $workflow->buildTransitionBlockerList($entity, $transition->getName())
        ){
//            dump($transitionBlockerList);
            /** @var \Symfony\Component\Workflow\TransitionBlocker $blocker */
            foreach ($transitionBlockerList as $blocker) {
                dump($blocker->getMessage());
            }
        }
    }

    dd("####");

    dd($workflow->getDefinition()->getTransitions());

    dump($workflow->can($entity, 'choose_city'));
    /*dump($workflow->getMetadataStore());
    dd($contextWorkflow->getValidationErrors());*/
//    dd($workflow->getEnabledTransitions($entity));
    dd($workflow);
    foreach ($workflow->getEnabledTransitions($entity) as $transition) {
        dump($workflow->buildTransitionBlockerList($entity, $transition->getName()));
    }
    dd("###");
//    dd($workflow->buildTransitionBlockerList($entity, 'choose_city'));

    $workflow->apply($entity, 'choose_city');

    dd("####");
    $entity->save();
});
