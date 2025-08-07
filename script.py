#!/usr/bin/env python3

import sys
import random
import sqlite3
import cellmap
from pathlib import Path
from datetime import datetime
from openpyxl import load_workbook
from cellmap import CELL_MAP

# Configurações fixas
DB_PATH        = Path(__file__).parent / "database.sqlite"
TEMPLATE_PATH  = Path(__file__).parent / "EXPORT.xlsx"
OUTPUT_PATTERN = "Resumo_Kamishibai_{period}_{rand}.xlsx"

# 1) Defina aqui, para cada combinação item_id + dia, a célula exata:
#    a chave deve ter o formato '{item_id}X{DD:02d}', ex: '1X01' para item_id=1 e dia=01.

# Query para buscar o status exato ('C', 'NC' ou 'NA')
STATUS_QUERY = """
SELECT status
  FROM activity_records
 WHERE record_date LIKE ?
   AND item_id = ?
 LIMIT 1;
"""

def fetch_status(db_path: Path, date_filter: str, item_id: int):
    """
    Retorna 'C', 'NC' ou 'NA' para aquele item e dia,
    ou None se não existir registro.
    """
    conn = sqlite3.connect(db_path)
    cur = conn.cursor()
    cur.execute(STATUS_QUERY, (date_filter, item_id))
    row = cur.fetchone()
    conn.close()
    return row[0] if row else None

def fill_excel(template_path: Path, output_path: Path, period: str):
    """
    Para cada chave em CELL_MAP, interpreta item_id e dia,
    busca o status no DB e escreve na célula definida.
    """
    wb = load_workbook(template_path)
    ws = wb.active

    for key, cell in CELL_MAP.items():
        # separa '1X01' → item_id_str='1', day_str='01'
        item_id_str, day_str = key.split('X')
        item_id = int(item_id_str)
        # monta filtro 'YYYY-MM-DD%'
        date_filter = f"{period}-{day_str}%"
        status = fetch_status(DB_PATH, date_filter, item_id) or ''
        ws[cell] = status

    wb.save(output_path)

def main():
    # recebe apenas o mês no formato YYYY-MM
    if len(sys.argv) != 2:
        print("Uso: python export_by_cell.py <YYYY-MM>")
        sys.exit(1)

    period = sys.argv[1]
    try:
        datetime.strptime(period, "%Y-%m")
    except ValueError:
        print("❌ Formato inválido. Use YYYY-MM, ex: 2025-08")
        sys.exit(1)

    if not TEMPLATE_PATH.exists():
        print(f"❌ Template não encontrado: {TEMPLATE_PATH}")
        sys.exit(1)

    rand = random.randint(1000, 9999)
    output_file = Path(OUTPUT_PATTERN.format(period=period, rand=rand))
    fill_excel(TEMPLATE_PATH, output_file, period)
    print(f"✅ Resumo gerado: {output_file.resolve()}")

if __name__ == '__main__':
    main()
