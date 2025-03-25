<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325211610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE membership_id_seq CASCADE');
        $this->addSql('ALTER TABLE wishlist_item DROP CONSTRAINT fk_6424f4e8fb8e54cd');
        $this->addSql('ALTER TABLE wishlist_item DROP CONSTRAINT fk_6424f4e8126f525e');
        $this->addSql('DROP TABLE wishlist_item');
        $this->addSql('ALTER TABLE item ADD wishlist_id INT NOT NULL');
        $this->addSql('ALTER TABLE item ADD description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EFB8E54CD FOREIGN KEY (wishlist_id) REFERENCES wishlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1F1B251EFB8E54CD ON item (wishlist_id)');
        $this->addSql('DROP INDEX uniq_86ffd285a76ed395');
        $this->addSql('ALTER TABLE membership DROP CONSTRAINT membership_pkey');
        $this->addSql('ALTER TABLE membership ADD wishlist_id INT NOT NULL');
        $this->addSql('ALTER TABLE membership DROP id');
        $this->addSql('ALTER TABLE membership ALTER user_id SET NOT NULL');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD285FB8E54CD FOREIGN KEY (wishlist_id) REFERENCES wishlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_86FFD285FB8E54CD ON membership (wishlist_id)');
        $this->addSql('CREATE INDEX IDX_86FFD285A76ED395 ON membership (user_id)');
        $this->addSql('ALTER TABLE membership ADD PRIMARY KEY (wishlist_id, user_id)');
        $this->addSql('ALTER TABLE purchase ALTER receipt_url DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE membership_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE wishlist_item (wishlist_id INT NOT NULL, item_id INT NOT NULL, PRIMARY KEY(wishlist_id, item_id))');
        $this->addSql('CREATE INDEX idx_6424f4e8126f525e ON wishlist_item (item_id)');
        $this->addSql('CREATE INDEX idx_6424f4e8fb8e54cd ON wishlist_item (wishlist_id)');
        $this->addSql('ALTER TABLE wishlist_item ADD CONSTRAINT fk_6424f4e8fb8e54cd FOREIGN KEY (wishlist_id) REFERENCES wishlist (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wishlist_item ADD CONSTRAINT fk_6424f4e8126f525e FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251EFB8E54CD');
        $this->addSql('DROP INDEX IDX_1F1B251EFB8E54CD');
        $this->addSql('ALTER TABLE item DROP wishlist_id');
        $this->addSql('ALTER TABLE item DROP description');
        $this->addSql('ALTER TABLE purchase ALTER receipt_url SET NOT NULL');
        $this->addSql('ALTER TABLE membership DROP CONSTRAINT FK_86FFD285FB8E54CD');
        $this->addSql('DROP INDEX IDX_86FFD285FB8E54CD');
        $this->addSql('DROP INDEX IDX_86FFD285A76ED395');
        $this->addSql('DROP INDEX membership_pkey');
        $this->addSql('ALTER TABLE membership ADD id SERIAL NOT NULL');
        $this->addSql('ALTER TABLE membership DROP wishlist_id');
        $this->addSql('ALTER TABLE membership ALTER user_id DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_86ffd285a76ed395 ON membership (user_id)');
        $this->addSql('ALTER TABLE membership ADD PRIMARY KEY (id)');
    }
}
