<?php

namespace App\SqlObject;

use Doctrine\DBAL\Connection;

final readonly class BaseTriggerFunction implements InstallableSqlObject, DependentInstallableObject
{
    public function getName(): string
    {
        return 'rikudou_notify_trigger';
    }

    public function install(Connection $connection): void
    {
        $connection->executeStatement(
            <<< SQL_CODE
            CREATE OR REPLACE FUNCTION {$this->getName()}() RETURNS trigger AS
            \$trigger$
            DECLARE
                rec           RECORD;
                payload       TEXT;
                column_name   TEXT;
                column_value  TEXT;
                payload_items jsonb := '{}'::jsonb;
                previous      jsonb := '{}'::jsonb;
                result_id     int;
            BEGIN
                CASE TG_OP
                    WHEN 'INSERT','UPDATE' THEN rec := NEW;
                    WHEN 'DELETE' THEN rec := OLD;
                    ELSE RAISE EXCEPTION 'Unknown TG_OP: "%". Should not occur!', TG_OP;
                    END CASE;
            
                IF TG_ARGV[0] IS NOT NULL THEN
                    FOREACH column_name IN ARRAY TG_ARGV
                        LOOP
                            EXECUTE format('SELECT $1.%I::TEXT', column_name)
                                INTO column_value
                                USING rec;
                            payload_items := payload_items || jsonb_build_object(column_name, column_value);
                        END LOOP;
                ELSE
                    payload_items := to_jsonb(rec);
                END IF;
                
                IF TG_OP = 'UPDATE' THEN 
                    IF TG_ARGV[0] IS NOT NULL THEN
                        FOREACH column_name IN ARRAY TG_ARGV
                            LOOP
                                EXECUTE format('SELECT $1.%I::TEXT', column_name)
                                    INTO column_value
                                    USING OLD;
                                previous := previous || jsonb_build_object(column_name, column_value);
                            END LOOP;
                    ELSE
                        previous := to_jsonb(OLD);
                    END IF;
                END IF;
            
                IF TG_OP = 'UPDATE' THEN
                    payload := json_build_object('timestamp', CURRENT_TIMESTAMP, 'operation', TG_OP, 'schema', TG_TABLE_SCHEMA, 'table', TG_TABLE_NAME, 'data', payload_items, 'previous', previous);
                ELSE
                    payload := json_build_object('timestamp', CURRENT_TIMESTAMP, 'operation', TG_OP, 'schema', TG_TABLE_SCHEMA, 'table', TG_TABLE_NAME, 'data', payload_items);
                END IF;
                
                if octet_length(payload) > 8000 then
                    insert into rikudou_webhooks_large_payloads(payload) values (payload) returning id into result_id;
                    PERFORM pg_notify('rikudou_event', result_id::text);
                else
                    PERFORM pg_notify('rikudou_event', payload);
                end if;
            
                RETURN rec;
            END;
            \$trigger$ LANGUAGE plpgsql;
            SQL_CODE,
        );
    }

    public function uninstall(Connection $connection): void
    {
        $connection->executeStatement("drop function {$this->getName()}");
    }

    public function getDependencies(): array
    {
        return [
            LargePayloadTable::class,
        ];
    }
}
