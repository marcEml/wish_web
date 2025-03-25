<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325224019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item (id SERIAL NOT NULL, wishlist_id INT NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, external_link VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1F1B251EFB8E54CD ON item (wishlist_id)');
        $this->addSql('CREATE TABLE membership (wishlist_id INT NOT NULL, user_id INT NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(wishlist_id, user_id))');
        $this->addSql('CREATE INDEX IDX_86FFD285FB8E54CD ON membership (wishlist_id)');
        $this->addSql('CREATE INDEX IDX_86FFD285A76ED395 ON membership (user_id)');
        $this->addSql('CREATE TABLE purchase (id SERIAL NOT NULL, user_id INT DEFAULT NULL, item_id INT DEFAULT NULL, message VARCHAR(255) NOT NULL, receipt_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6117D13BA76ED395 ON purchase (user_id)');
        $this->addSql('CREATE INDEX IDX_6117D13B126F525E ON purchase (item_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, password_salt INT DEFAULT NULL, status VARCHAR(255) NOT NULL, is_locked BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE website (id SERIAL NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE wishlist (id SERIAL NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9CE12A31A76ED395 ON wishlist (user_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EFB8E54CD FOREIGN KEY (wishlist_id) REFERENCES wishlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD285FB8E54CD FOREIGN KEY (wishlist_id) REFERENCES wishlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD285A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B126F525E FOREIGN KEY (item_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wishlist ADD CONSTRAINT FK_9CE12A31A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251EFB8E54CD');
        $this->addSql('ALTER TABLE membership DROP CONSTRAINT FK_86FFD285FB8E54CD');
        $this->addSql('ALTER TABLE membership DROP CONSTRAINT FK_86FFD285A76ED395');
        $this->addSql('ALTER TABLE purchase DROP CONSTRAINT FK_6117D13BA76ED395');
        $this->addSql('ALTER TABLE purchase DROP CONSTRAINT FK_6117D13B126F525E');
        $this->addSql('ALTER TABLE wishlist DROP CONSTRAINT FK_9CE12A31A76ED395');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE membership');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE website');
        $this->addSql('DROP TABLE wishlist');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
