<?php


class AppController extends Controller
{
    public function testQuery()
    {
        $q = new Query('user');
        
        echo $q->build() . "<br>";
        
        $q->filter('username', 'not like', 'dem%')->group('last_name');
        
        echo $q->build() . "<br>";
        
        $q->filter('id', 'in', array(1,2,'haha'))->order('id', 'desc');
        
        echo $q->build() . "<br>";
        
        $q->run();
        
        echo "<pre>";
        
        print_r($q);
    }
    
    public function testOrm()
    {
        $u1 = new User();
        $u1->save();
        $u1->first_name = 'Bob';
        $u1->save();

        $u2 = new User();
        $u2->first_name = 'James';
        $u2->save();

        $t1 = new Thing();
        $t1->save();

        $s1 = new Stuff();
        $s1->thing = $t1;
        $s1->save();
        
        $t1->users[] = $u2;
        $t1->users[] = $u1;
        
        $t1->delete();
    }
    
    public function testConMethod()
    {
        for ($i=1; $i<9; $i++)
        {
            $thing = new Thing($i);
            $form = new Form($thing);
            $form->render();
        }
        /*
        $users = array();
        
        for ($i=0; $i<100; $i++)
        {
            $user = new User();
            $user->first_name = 'abc';
            $user->last_name = 'xyz';
            $user->username = 'axyz';
            $user->password = 'thisisyourpass';
            $user->save();
            $users[$i] = $user;
        }
        
        $things = array();
        
        for ($i=0; $i<100; $i++)
        {
            $thing = new Thing();
            $thing->save();
            
            for ($j=0; $j<10; $j++)
            {
                $thing->users[] = $users[$j];
            }
            
            $things[] = $thing;
        }
        */
    }
    
    public function wordMethod($id, $slug)
    {
        echo "In test controller.<br />Super awesome wordy stuff!<br />Passed ID: $id<br />Passed slug: $slug<br />";
    }
    
    public function inputMethod($class, $id)
    {
        /*
        $text = new TextField();
        $text->label = "(Long) Text field";
        $text->name = "text_field";
        $text->value = "It's the Value of the Textingness";
        
        Response::$context['text'] = $text;
        
        $char = new CharField();
        $char->label = "Varchar field";
        $char->name = "char_field";
        $char->value = "It's the Value of the charingness";
        
        Response::$context['char'] = $char;
        
        $objs = new User();
        $objs = $objs->getAll();
        
        if (!count($objs))
        {
            $u = new User(array(
                'username' => 'admin',
                'password_hash' => md5('admin'),
                ));
            $u->save();
            
            $u = new User(array(
                'username' => 'phu',
                'password_hash' => md5('phu'),
                ));
            $u->save();
            
            $objs = $u->getAll();
        }
        
        $key = new ForeignKeyField();
        $key->label = "Foreign Key field";
        $key->name = "key_field";
        $key->class = "user";
        $key->value = $objs[count($objs) - 1];
        
        Response::$context['key'] = $key;
        
        Response::renderTemplate('appname', 'input_test.php');
        */
    }
}
