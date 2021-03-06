<?php

namespace Concrete\Package\HwSimpleTestimonials\Controller\SinglePage\Dashboard;

use HwSimpleTestimonials\Entity\Testimonial;
use HwSimpleTestimonials\Entity\TestimonialList;
use Concrete\Core;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Support\Facade\Url;

class Testimonials extends \Concrete\Core\Page\Controller\DashboardPageController
{
    public function view()
    {
        $list = new TestimonialList();
        $factory = new PaginationFactory(\Request::getInstance());
        $paginator = $factory->createPaginationObject($list);
        $pagination = $paginator->renderDefaultView();
        $this->set('entries', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

    }

    public function add()
    {
        $this->set('pageTitle', t('Add Testimonial'));
        $testimonial = new Testimonial();
        $this->set('testimonials', $testimonial);

    }

    public function edit($id = 0)
    {
        $this->set('pageTitle', t('Edit Testimonial'));
        $testimonial = Testimonial::getByID($id);
        $this->set('sID', $testimonial->getSID());
        $this->set('author', $testimonial->getAuthor());
        $this->set('company', $testimonial->getCompany());
        $this->set('testimonial', $testimonial->getTestimonial());
        $this->set('extra', $testimonial->getExtra());
    }

    public function submit()
    {
        $data = $this->post();
        if (!$this->token->validate('submit')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has() && $this->isPost()) {
            if ($this->post('sID')) {
                $testimonial = Testimonial::getByID($this->post('sID'));
            } else {
                $testimonial = new Testimonial();
            }
            $testimonial->setAuthor($this->post('author'));
            $testimonial->setCompany($this->post('company'));
            $testimonial->setTestimonial($this->post('testimonial'));
            $testimonial->setExtra($this->post('extra'));
            $testimonial->save();

            $this->flash('success', t('Testimonial saved successfully.'));

            $factory = $this->app->make(ResponseFactory::class);
            return $factory->redirect(Url::to('/dashboard/testimonials'));
        }
    }

    public function SortOrder()
    {

        $uats = $_REQUEST['tID'];

        if (is_array($uats)) {
            $uats = array_filter($uats, 'is_numeric');
        }

        if (count($uats)) {
            $db = $this->app->make('database')->connection();
            for ($i = 0; $i < count($uats); $i++) {
                $v = array($uats[$i]);
                $db->query("update bthwsimpletestimonial set SortOrder = {$i} where sID = ?", $v);
            }
        }
    }

    public function delete()
    {
        if (!$this->token->validate('delete')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has() && $this->isPost()) {
            $e = Testimonial::getByID($this->post('sID'));
            if (is_object($e)) {
                $e->delete();
            }
            $this->flash('success', t('Testimonial deleted successfully.'));

            $factory = $this->app->make(ResponseFactory::class);
            return $factory->redirect(Url::to('/dashboard/testimonials'));


        }
        $this->view();
    }
}