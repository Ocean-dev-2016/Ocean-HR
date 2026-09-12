<?php

namespace Database\Seeders;

use App\Models\ManageEmail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ManageEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $ManageEmail = [
            // [
            //     'company_id' => '4',
            //     'module' => 'Inward',
            //     'ntype' => 'inward_deleted',
            //     'name' => 'Inward deleted',
            //     'subject' => 'Inward [inward_no] Has Been Deleted',
            //     'body' => '<p><strong>Dear [customer_name],</strong></p><p>We would like to inform you that your Inward <strong>([inward_no])</strong> dated <strong>[inward_date]</strong> has been <strong>deleted</strong> by <strong>[company_name]</strong>.</p><p>Below are your details for reference:</p><p>Inward No: <strong>[inward_no]&nbsp;</strong></p><p>Inward Date : <strong>[inward_date]&nbsp;</strong></p><p>Purchase Order No: [<strong>purchase_order_no</strong>]&nbsp;</p><p>Purchase Order Date: [<strong>purchase_order_date</strong>]&nbsp;</p><p>Company Name: [<strong>company_name</strong>]&nbsp;</p><p>&nbsp;</p><p>Our team will reach out to you shortly for the next steps.</p><p>If you have any questions or require further assistance, feel free to contact us.</p><p><br><strong>Best regards,</strong></p><p>[<strong>company_name</strong>]&nbsp;</p>',
            //     'status' => 'active',
            //     'created_by' => 1,
            // ],

        ];

        foreach ($ManageEmail as $subEmail) {
            ManageEmail::create($subEmail);
        }
    }
}
