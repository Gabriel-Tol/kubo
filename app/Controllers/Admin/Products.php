<?php

declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use InvalidArgumentException;

class Products extends BaseController
{
    public function index(): string
    {
        helper('text');
        $db=db_connect(); $products=$db->table('products p')->select('p.*, c.name AS category_name')->join('categories c','c.id=p.category_id','left')->orderBy('p.archived_at','ASC')->orderBy('p.name')->get()->getResultArray();
        foreach($products as &$product){$product['variants']=$db->table('product_variants')->where('product_id',$product['id'])->get()->getResultArray();} unset($product);
        return view('admin/products',['title'=>'Produk','products'=>$products,'categories'=>$db->table('categories')->orderBy('sort_order')->get()->getResultArray()]);
    }
    public function form(?int $id=null): string
    {
        $db=db_connect(); $product=null; $variant=null;
        if($id!==null){$product=$db->table('products')->where('id',$id)->get()->getRowArray(); if($product===null)throw PageNotFoundException::forPageNotFound(); $variant=$db->table('product_variants')->where('product_id',$id)->orderBy('id')->get()->getRowArray();}
        return view('admin/product-form',['title'=>$id===null?'Tambah Produk':'Edit Produk','product'=>$product,'variant'=>$variant,'categories'=>$db->table('categories')->orderBy('sort_order')->get()->getResultArray()]);
    }
    public function save(?int $id=null): mixed
    {
        helper('text');
        $name=trim((string)$this->request->getPost('name')); $description=trim((string)$this->request->getPost('description')); $composition=trim((string)$this->request->getPost('composition')); $categoryId=(int)$this->request->getPost('category_id'); $price=(int)$this->request->getPost('price'); $stock=(int)$this->request->getPost('stock');
        if($name===''||$description===''||$composition===''||$categoryId<1||$price<1||$stock<0)return redirect()->back()->withInput()->with('error','Lengkapi nama, deskripsi, komposisi, kategori, harga di atas Rp0, dan stok yang valid.');
        $image=$this->request->getFile('image'); $oldImage=null;
        if($id!==null){$existing=db_connect()->table('products')->where('id',$id)->get()->getRowArray();if($existing===null)throw PageNotFoundException::forPageNotFound();$oldImage=db_connect()->table('product_images')->where('product_id',$id)->where('is_primary',1)->get()->getRowArray();}
        if(($image===null||$image->getError()===UPLOAD_ERR_NO_FILE)&&$id===null)return redirect()->back()->withInput()->with('error','Foto produk utama wajib diunggah.');
        $newPath=null;
        if($image!==null&&$image->getError()!==UPLOAD_ERR_NO_FILE){
            if(!$image->isValid()||$image->getSize()>3*1024*1024||!in_array(strtolower((string)$image->getMimeType()),['image/jpeg','image/png'],true))return redirect()->back()->withInput()->with('error','Foto harus JPG/PNG dengan ukuran maksimal 3 MB.');
            $directory=FCPATH.'assets'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'uploads';if(!is_dir($directory))mkdir($directory,0755,true);
            $extension=$image->getMimeType()==='image/png'?'png':'jpg';$filename=bin2hex(random_bytes(18)).'.'.$extension;$image->move($directory,$filename);$newPath='assets/images/uploads/'.$filename;
        }
        $db=db_connect();$now=date('Y-m-d H:i:s');$slug=url_title($name,'-',true);$existingSlug=$db->table('products')->where('slug',$slug)->where('id <>',$id??0)->countAllResults();if($existingSlug>0)$slug.='-'.substr(bin2hex(random_bytes(3)),0,6);
        $fields=['category_id'=>$categoryId,'name'=>$name,'slug'=>$slug,'description'=>$description,'composition'=>$composition,'allergens'=>trim((string)$this->request->getPost('allergens')),'storage_note'=>trim((string)$this->request->getPost('storage_note')),'is_active'=>$this->request->getPost('is_active')==='1','updated_at'=>$now];
        $db->transBegin();
        if($id===null){$fields['created_at']=$now;$fields['archived_at']=null;$db->table('products')->insert($fields);$id=(int)$db->insertID();}else{$db->table('products')->where('id',$id)->update($fields);}
        $variant=$db->table('product_variants')->where('product_id',$id)->orderBy('id')->get()->getRowArray();$variantFields=['label'=>trim((string)$this->request->getPost('variant_label'))?:'Ukuran reguler','price'=>$price,'stock'=>$stock,'is_active'=>true,'updated_at'=>$now];
        if($variant===null){$variantFields+=['product_id'=>$id,'created_at'=>$now];$db->table('product_variants')->insert($variantFields);}else{$db->table('product_variants')->where('id',$variant['id'])->update($variantFields);}
        if($newPath!==null){if($oldImage!==null)$db->table('product_images')->where('id',$oldImage['id'])->update(['path'=>$newPath,'alt_text'=>$name,'created_at'=>$now]);else$db->table('product_images')->insert(['product_id'=>$id,'path'=>$newPath,'alt_text'=>$name,'sort_order'=>0,'is_primary'=>true,'created_at'=>$now]);}
        if($db->transStatus()===false){$db->transRollback();return redirect()->back()->withInput()->with('error','Produk gagal disimpan.');}$db->transCommit();
        return redirect()->to(site_url('admin/produk'))->with('success','Produk disimpan.');
    }
    public function archive(int $id): mixed
    {
        $db=db_connect();$product=$db->table('products')->where('id',$id)->get()->getRowArray();if($product===null)throw PageNotFoundException::forPageNotFound();$archived=$product['archived_at']===null;
        $db->table('products')->where('id',$id)->update(['is_active'=>!$archived,'archived_at'=>$archived?date('Y-m-d H:i:s'):null,'updated_at'=>date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('admin/produk'))->with('success',$archived?'Produk diarsipkan.':'Produk dipulihkan.');
    }
    public function category(): mixed
    {
        helper('text');
        $name=trim((string)$this->request->getPost('name'));if($name===''||mb_strlen($name)>100)return redirect()->back()->with('error','Nama kategori wajib diisi.');$slug=url_title($name,'-',true);$db=db_connect();if($db->table('categories')->where('slug',$slug)->countAllResults()>0)return redirect()->back()->with('error','Kategori tersebut sudah ada.');$db->table('categories')->insert(['name'=>$name,'slug'=>$slug,'sort_order'=>100,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);return redirect()->to(site_url('admin/produk'))->with('success','Kategori ditambahkan.');
    }
    public function renameCategory(int $id): mixed
    {
        helper('text');
        $db = db_connect();
        $category = $db->table('categories')->where('id', $id)->get()->getRowArray();
        if ($category === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        $name = trim((string) $this->request->getPost('name'));
        if ($name === '' || mb_strlen($name) > 100) {
            return redirect()->back()->with('error', 'Nama kategori wajib diisi (maksimal 100 karakter).');
        }
        $slug = url_title($name, '-', true);
        $duplicate = $db->table('categories')->where('slug', $slug)->where('id <>', $id)->countAllResults();
        if ($duplicate > 0) {
            return redirect()->back()->with('error', 'Kategori tersebut sudah ada.');
        }
        $db->table('categories')->where('id', $id)->update([
            'name' => $name, 'slug' => $slug, 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('admin/produk'))->with('success', 'Kategori diperbarui.');
    }

    public function deleteCategory(int $id): mixed
    {
        $db = db_connect();
        if ($db->table('categories')->where('id', $id)->countAllResults() === 0) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($db->table('products')->where('category_id', $id)->countAllResults() > 0) {
            return redirect()->back()->with('error', 'Kategori tidak dapat dihapus selama masih digunakan produk.');
        }
        $db->table('categories')->where('id', $id)->delete();
        return redirect()->to(site_url('admin/produk'))->with('success', 'Kategori dihapus.');
    }
}