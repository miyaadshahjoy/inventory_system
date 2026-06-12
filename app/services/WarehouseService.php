<?php

class WarehouseService
{
    public static function getAllWarehouses()
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT *
            FROM warehouses
            ORDER BY created_at DESC
        ");
        $statement->execute();
        $result = $statement->get_result();
        $warehouses = $result->fetch_all(MYSQLI_ASSOC);
        return $warehouses;
    }

    public static function getAllActiveWarehouses()
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT *
            FROM warehouses
            WHERE warehouse_status = 'ACTIVE'
            ORDER BY created_at DESC
        ");
        $statement->execute();
        $result = $statement->get_result();
        $warehouses = $result->fetch_all(MYSQLI_ASSOC);
        return $warehouses;
    }

    public static function getWarehouseById(int $id)
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT *
            FROM warehouses
            WHERE id = ?
            AND warehouse_status = 'ACTIVE'
            FOR UPDATE
        ");
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        if ($result->num_rows === 0) {
            throw new ValidationException(
                "Warehouse does not exist or is inactive.",
            );
        }
        $warehouse = $result->fetch_assoc();
        return $warehouse;
    }

    public static function createWarehouse(array $data)
    {
        $name = $data["name"];
        $location = $data["location"];

        $conn = Database::connect();
        $statement = $conn->prepare("
            INSERT INTO warehouses(name, location)
            VALUES (?, ?)
        ");

        $statement->bind_param("ss", $name, $location);
        try {
            $statement->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                throw new ValidationException(
                    "Warehouse with the same name already exists.",
                );
            }
        }
    }

    public static function updateWarehouse(int $warehouse_id, array $data)
    {
        $name = $data["name"];
        $location = $data["location"];

        $conn = Database::connect();

        # A) Check if warehouse exists
        $warehouse = self::getWarehouseById($warehouse_id);
        if (!$warehouse) {
            throw new ValidationException("Warehouse not found");
        }

        # B) Update warehouse in DB
        $statement = $conn->prepare("
            UPDATE warehouses
            SET name = ?, location = ?
            WHERE id = ?
        ");
        $statement->bind_param("ssi", $name, $location, $warehouse_id);
        try {
            $statement->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                throw new ValidationException(
                    "Warehouse with the same name already exists.",
                );
            }
        }
    }

    public static function deleteWarehouse(int $warehouse_id)
    {
        # Check if warehouse exists
        $warehouse = self::getWarehouseById($warehouse_id);
        if (!$warehouse) {
            throw new ValidationException("Warehouse not found");
        }

        # Delete warehouse
        # Update warehouse status: ACTIVE -> INACTIVE
        $conn = Database::connect();
        $statement = $conn->prepare("
            UPDATE warehouses
            SET warehouse_status = 'INACTIVE'
            WHERE id = ?
        ");
        $statement->bind_param("i", $warehouse_id);
        $statement->execute();
    }
}
