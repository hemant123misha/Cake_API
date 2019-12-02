<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;


/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 *
 * @method \App\Model\Entity\Article[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ArticlesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
	
    public function index()
    {
         $this->loadModel('Tags');
		 $mdata = array();
         $tags       = TableRegistry::getTableLocator()->get('Tags');
		 $articles   = $this->Articles->find('all');
        
			foreach ($articles as $key=>$article) {
						  $query   = $tags->find()->select(['name'])->where(['a_id' => $article->id]);
						  $mdata[] = $article;
						  $mdata[$key]->tags = $query;
			}
		
        $this->set([
            'articles' => $mdata,
            '_serialize' => ['articles']
        ]);
    }

    /**
     * View method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $article = $this->Articles->get($id, [
            'contain' => []
        ]);

        $this->set('article', $article);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
		$article            = $this->Articles->newEntity();
        $conn 				= ConnectionManager::get('default');
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
			$stags 	 = explode(',', $_REQUEST['tags']);
            
			$sql1 = $conn->execute("SELECT * FROM `articles` WHERE title = '".$_REQUEST['title']."' AND author LIKE '%".$_REQUEST['author']."%'");
			$results = $sql1->fetchAll('assoc');
				if(count($results) == 0){
				   $res  = $this->Articles->save($article);
				   if($res) {
					$r_id = $res->id;
					foreach($stags as $row){
							$sql = $conn->execute("SELECT * FROM `tags` WHERE a_id = '".$r_id."' AND name LIKE '%".$row."%'");
							$results = $sql->fetchAll('assoc');
						if(count($results) > 0){
							$stmt = $conn->execute("UPDATE `tags` SET `name`='".$row."',`a_id`='".$r_id."' WHERE a_id = '".$r_id."' AND name = '".$row."')");
						}else{
							$stmt = $conn->execute("INSERT INTO `tags`(`name`, `a_id`) VALUES ('".$row."','".$r_id."')");
						}	
					}
					$mres = 'The article has been saved.';
					$st = 'true';
				}
			}else{
				 $mres = 'The article could not be saved. Please, try again.';
				 $st = 'false';
			}
        }
        $this->set([
            'message' => $mres,
            'status'  => $st,
            '_serialize' => ['message','status','mstatus']
        ]);
    }

    /**
     * Edit method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $conn 				= ConnectionManager::get('default');
		$id 				= $_REQUEST['id'];
		$article 			= $this->Articles->get($id, ['contain' => []]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            $res     = $this->Articles->save($article);
			$stags 	 = explode(',', $_REQUEST['tags']);
			if($res) {
                $r_id = $id;
				foreach($stags as $row){
						$sql = $conn->execute("SELECT * FROM `tags` WHERE a_id = '".$r_id."' AND name LIKE '%".$row."%'");
						$results = $sql->fetchAll('assoc');
					if(count($results) > 0){
						$stmt = $conn->execute("UPDATE `tags` SET `name`='".$row."',`a_id`='".$r_id."' WHERE a_id = '".$r_id."' AND name = '".$row."')");
					}else{
						$stmt = $conn->execute("INSERT INTO `tags`(`name`, `a_id`) VALUES ('".$row."','".$r_id."')");
					}	
				}
				$mres = 'The article has been updated.';
				$st = 'true';
            }else{
				 $mres = 'The article could not be updated. Please, try again.';
				 $st = 'false';
			}
        }
        $this->set([
            'message' => $mres,
            'status'  => $st,
            '_serialize' => ['message','status','mstatus']
        ]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $id = $_REQUEST['id'];
		$this->request->allowMethod(['post', 'delete']);
		$article = $this->Articles->get($id);
		
        if ($this->Articles->delete($article)) {
            $conn = ConnectionManager::get('default');
			$stmt = $conn->execute("DELETE FROM `tags` WHERE a_id = '".$id."'");
			$mres = 'The article has been deleted.';
			$st   = 'true';
			
        } else {
            $mres = 'The article could not be deleted. Please, try again.';
			$st   = 'false';
        }

        $this->set([
            'message' => $mres,
            'status'  => $st,
            '_serialize' => ['message','status','mstatus']
        ]);
    }

}
