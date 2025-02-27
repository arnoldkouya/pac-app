<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tutorial;
use App\Models\Technology;
use Bow\Database\Collection;

class TutorialService
{
    /**
     * The default number of tutoriel in one page
     *
     * @var int
     */
    public const PER_PAGE = 9;

    /**
     * The tutorial model instance
     *
     * @var Tutorial
     */
    private $tutorial;

    /**
     * The technologie model instance
     *
     * @var Technology
     */
    private $technologie;

    /**
     * TutorialController constructor.
     *
     * @return void
     */
    public function __construct(Tutorial $tutorial, Technology $technologie)
    {
        $this->tutorial = $tutorial;
        $this->technologie = $technologie;
    }

    /**
     * Get the tutoriel
     *
     * @param  int $tutorial_id
     * @return Tutorial
     */
    public function find($tutorial_id)
    {
        return $this->tutorial->find($tutorial_id);
    }

    /**
     * Get tutorial pagination
     *
     * @param  string $technologie
     * @param  string $url
     * @param  int    $author
     * @return Collection
     */
    public function getTutorialPagination($technologie, $url, $author_id = null)
    {
        if (is_null($technologie)) {
            $tutorials = $this->tutorial
                ->select(Tutorial::SELECT_FIELD)
                ->whereNull('graph_id')
                ->orderBy('created_at', 'desc')
                ->where('published', true);
        } else {
            $tutorials = $this->technologie
                ->findOne('slug', $technologie)
                ->tutorials()
                ->whereNull('graph_id')
                ->select(Tutorial::SELECT_FIELD);
        }

        if (! is_null($author_id)) {
            $tutorials = $tutorials->where('author_id', (int) $author_id);
        }

        $tutorials = $tutorials->paginate(static::PER_PAGE);

        return $tutorials;
    }

    /**
     * Get the single tutorial
     *
     * @param  string $slug
     * @param  int    $tutorial_id
     * @return mixed
     */
    public function getSingleTutorial($slug, $tutorial_id)
    {
        /** @var User */
        $user = auth()->user();
        $tutorial = $this->getTutorial($slug, $tutorial_id);

        if (is_null($tutorial)) {
            app_abort(404);
        }

        $form_curriculum = ! is_null($tutorial->graph_id);
        $tutorials = [];
        $followed = false;
        $ended = ! is_null($user) && $form_curriculum ? $tutorial->hasProgress($user) : false;
        $curriculum = null;

        $comments = $tutorial->comments()->orderBy('created_at', 'desc')->where('parent_id', 0)->get();

        if (! $form_curriculum) {
            $tutorials = $tutorial->technologie->tutorials()->take(3)
                ->whereNull('graph_id')
                ->orderBy('created_at', 'desc')
                ->where('id', '!=', $tutorial->id)
                ->where('published', true)->get();

            return compact('tutorial', 'tutorials', 'form_curriculum', 'curriculum', 'followed', 'ended');
        }

        $curriculum = $tutorial->graph?->section?->curriculum;

        if ($user) {
            $followed = $user->hasBookmark($curriculum);
        }

        if (! $tutorial->isOneTimePayment()) {
            return compact('tutorial', 'tutorials', 'form_curriculum', 'curriculum', 'followed', 'ended');
        }

        if (! ($curriculum->isOneTimePayment() || $curriculum->isPremium())) {
            return compact('tutorial', 'tutorials', 'form_curriculum', 'curriculum', 'followed', 'ended');
        }

        if (is_null($user)) {
            $redirect = route('tutorial.reader', ['slug' => $tutorial->slug, 'id' => $tutorial->id]);
            $route = 'login';

            return redirect()
                ->route($route, isset($redirect) ? compact('redirect') : [])
                ->withFlash('error', 'Connectez-vous pour accéder à ce contenu !');
        }

        if (! $user->hasPurchased($curriculum)) {
            return redirect()
                ->route('payment.element', ['slug' => $curriculum->slug, 'id' => $curriculum->id, 'type' => 'curriculums', ...(isset($redirect) ? compact('redirect') : [])])
                ->withFlash('error', 'Vous devez payer pour accéder à ce contenu');
        }
    }

    /**
     * Receive Tutorial
     *
     * @param string $slug
     * @param int $tutorial_id
     * @return Tutorial
     */
    private function getTutorial($slug, $tutorial_id)
    {
        return $this->tutorial
            ->where('published', 1)
            ->where('slug', $slug)
            ->where('id', (int) $tutorial_id)
            ->first();
    }
}
