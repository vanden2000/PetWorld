<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::updateOrCreate(
            ['email' => 'admin@petworld.test'],
            [
                'name' => 'PetWorld Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ],
        );

        $blogs = [
            [
                'category_slug' => 'cham-soc-thu-cung',
                'title' => 'Cach cham soc cho con moi ve nha trong 7 ngay dau',
                'slug' => 'cach-cham-soc-cho-con-moi-ve-nha-trong-7-ngay-dau',
                'description' => 'Huong dan cham soc cho con moi ve nha: khong gian nghi ngoi, lich an, ve sinh, theo doi suc khoe va cach giup be lam quen voi gia dinh.',
                'image' => 'blogs/cham-soc-cho-con-moi-ve-nha.jpg',
                'content' => $this->article(
                    'Cach cham soc cho con moi ve nha trong 7 ngay dau',
                    'Bay ngay dau tien la giai doan quan trong de cho con lam quen voi mui, am thanh va lich sinh hoat moi. Chu nuoi nen chuan bi mot khu vuc yen tinh, co dem nam, bat nuoc rieng va do choi an toan.',
                    [
                        'Giu lich an on dinh, khong doi thuc an dot ngot trong nhung ngay dau.',
                        'Dat cho ngu o noi thoang, am vua phai va tranh tieng on lon.',
                        'Theo doi phan, muc nang luong, ho hap va bieu hien bo an.',
                    ],
                    'Neu be co dau hieu non lien tuc, tieu chay, met moi hoac bo an hon 24 gio, hay lien he bac si thu y. Viec tiem phong va tay giun can duoc sap xep theo lich tu van chuyen mon.',
                ),
                'view_count' => 42,
            ],
            [
                'category_slug' => 'dinh-duong',
                'title' => 'Chon thuc an hat cho meo truong thanh theo nhu cau dinh duong',
                'slug' => 'chon-thuc-an-hat-cho-meo-truong-thanh-theo-nhu-cau-dinh-duong',
                'description' => 'Kinh nghiem chon thuc an hat cho meo truong thanh dua tren do tuoi, can nang, muc van dong, tinh trang long da va kha nang tieu hoa.',
                'image' => 'blogs/chon-thuc-an-hat-cho-meo-truong-thanh.jpg',
                'content' => $this->article(
                    'Chon thuc an hat cho meo truong thanh theo nhu cau dinh duong',
                    'Meo truong thanh can khau phan can bang giua dam, chat beo, vitamin, khoang chat va nuoc. Khi chon thuc an hat, chu nuoi nen doc bang thanh phan, do dam, nang luong va khuyen nghi khau phan tren bao bi.',
                    [
                        'Chon cong thuc phu hop voi meo trong nha, meo nang dong hoac meo can kiem soat can nang.',
                        'Uu tien san pham co thong tin thanh phan ro rang va han su dung day du.',
                        'Doi thuc an tu tu trong 7 den 10 ngay de he tieu hoa thich nghi.',
                    ],
                    'Luon dat nuoc sach gan khu vuc an vi thuc an hat co do am thap. Neu meo co tien su soi tiet nieu, di ung hoac benh man tinh, can hoi y kien bac si thu y truoc khi doi khau phan.',
                ),
                'view_count' => 38,
            ],
            [
                'category_slug' => 'kinh-nghiem-mua-sam',
                'title' => 'Kinh nghiem mua phu kien an toan cho cho meo',
                'slug' => 'kinh-nghiem-mua-phu-kien-an-toan-cho-cho-meo',
                'description' => 'Checklist mua phu kien cho cho meo: chat lieu, kich thuoc, do ben, cach ve sinh va cac dau hieu can thay moi de dam bao an toan.',
                'image' => 'blogs/kinh-nghiem-mua-phu-kien-an-toan.jpg',
                'content' => $this->article(
                    'Kinh nghiem mua phu kien an toan cho cho meo',
                    'Phu kien nhu day dat, vong co, bat an, do choi va nha ve sinh can phu hop voi kich thuoc, thoi quen va muc do van dong cua thu cung. Mot mon do dung tot phai de ve sinh, khong co canh sac va khong gay kho chiu khi su dung lau.',
                    [
                        'Do kich thuoc co, nguc hoac khu vuc su dung truoc khi mua.',
                        'Kiem tra duong may, khoa cai, mui vat lieu va do chac cua san pham.',
                        'Thay moi khi phu kien bi nut, soi, bien dang hoac kho lam sach.',
                    ],
                    'Khong nen mua phu kien chi dua tren mau sac. Hay uu tien an toan, do vua van va kha nang bao tri hang ngay. Voi do choi, nen quan sat thu cung trong nhung lan su dung dau.',
                ),
                'view_count' => 34,
            ],
            [
                'category_slug' => 'cham-soc-thu-cung',
                'title' => 'Lich tam va ve sinh long cho thu cung tai nha',
                'slug' => 'lich-tam-va-ve-sinh-long-cho-thu-cung-tai-nha',
                'description' => 'Goi y lich tam, chai long, ve sinh tai mong va cham soc da long cho thu cung tai nha theo tung nhu cau sinh hoat.',
                'image' => 'blogs/lich-tam-va-ve-sinh-long-thu-cung.jpg',
                'content' => $this->article(
                    'Lich tam va ve sinh long cho thu cung tai nha',
                    'Lich tam cua thu cung khong nen co dinh giong nhau cho moi be. Giong loai, do dai long, moi truong song va tinh trang da se quyet dinh tan suat cham soc phu hop.',
                    [
                        'Chai long dinh ky giup giam roi long va phat hien ve, vet thuong som.',
                        'Dung sua tam danh rieng cho thu cung, khong dung dau goi cua nguoi.',
                        'Lau kho tai va ke chan sau khi tam de han che am uot.',
                    ],
                    'Neu da bi do, co mui bat thuong hoac thu cung gap phan ung sau khi tam, nen ngung san pham dang dung va tham khao bac si thu y.',
                ),
                'view_count' => 26,
            ],
            [
                'category_slug' => 'dinh-duong',
                'title' => 'Cach ket hop pate va thuc an hat trong bua an hang ngay',
                'slug' => 'cach-ket-hop-pate-va-thuc-an-hat-trong-bua-an-hang-ngay',
                'description' => 'Huong dan ket hop pate va thuc an hat de tang do ngon mieng, bo sung do am va van kiem soat tong nang luong moi ngay.',
                'image' => 'blogs/ket-hop-pate-va-thuc-an-hat.jpg',
                'content' => $this->article(
                    'Cach ket hop pate va thuc an hat trong bua an hang ngay',
                    'Ket hop pate voi thuc an hat co the giup bua an hap dan hon va tang do am cho khau phan. Tuy vay, chu nuoi can tinh tong nang luong de tranh cho an qua muc.',
                    [
                        'Bat dau voi mot luong pate nho de theo doi kha nang tieu hoa.',
                        'Giam bot luong hat neu da bo sung pate trong cung bua an.',
                        'Khong de pate ngoai nhiet do phong qua lau sau khi mo.',
                    ],
                    'Nen chon pate va hat phu hop cung do tuoi, loai thu cung va tinh trang suc khoe. Voi be co da day nhay cam, hay doi thuc don cham va ghi lai phan ung sau moi bua.',
                ),
                'view_count' => 22,
            ],
        ];

        foreach ($blogs as $blog) {
            $category = BlogCategory::where('slug', $blog['category_slug'])->firstOrFail();

            Blog::updateOrCreate(
                ['slug' => $blog['slug']],
                [
                    'blog_category_id' => $category->id,
                    'user_id' => $author->id,
                    'title' => $blog['title'],
                    'description' => $blog['description'],
                    'content' => $blog['content'],
                    'view_count' => $blog['view_count'],
                    'image' => $blog['image'],
                    'status' => 'active',
                ],
            );
        }
    }

    private function article(string $title, string $intro, array $highlights, string $note): string
    {
        $items = implode('', array_map(
            static fn (string $highlight): string => "<li>{$highlight}</li>",
            $highlights,
        ));

        return <<<HTML
<article>
    <h2>{$title}</h2>
    <p>{$intro}</p>
    <h3>Diem chinh can nho</h3>
    <ul>{$items}</ul>
    <h3>Luu y tu PetWorld</h3>
    <p>{$note}</p>
</article>
HTML;
    }
}
