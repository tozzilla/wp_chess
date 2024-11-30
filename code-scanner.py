import os
import datetime

def is_code_file(filename):
    """
    Determina se un file è un file di codice in base all'estensione.
    Aggiungere altre estensioni secondo necessità.
    """
    code_extensions = {
        '.py', '.java', '.cpp', '.c', '.h', '.hpp', '.js', '.ts',
        '.html', '.css', '.php', '.rb', '.go', '.rs', '.swift',
        '.kt', '.scala', '.sh', '.ps1', '.sql'
    }
    return os.path.splitext(filename)[1].lower() in code_extensions

def scan_directory(start_path='.'):
    """
    Scansiona ricorsivamente una directory e raccoglie il contenuto di tutti i file di codice.
    Restituisce un dizionario organizzato per cartelle.
    """
    result = {}
    
    # Converti il percorso in assoluto per avere riferimenti completi
    start_path = os.path.abspath(start_path)
    
    for root, dirs, files in os.walk(start_path):
        # Ignora le cartelle nascoste e le cartelle comuni da escludere
        dirs[:] = [d for d in dirs if not d.startswith('.') and d not in {'venv', 'node_modules', '__pycache__', 'dist', 'build'}]
        
        # Filtra solo i file di codice
        code_files = [f for f in files if is_code_file(f) and not f.startswith('.')]
        
        if code_files:
            # Usa il percorso relativo come chiave
            relative_path = os.path.relpath(root, start_path)
            result[relative_path] = {}
            
            for file in code_files:
                try:
                    file_path = os.path.join(root, file)
                    with open(file_path, 'r', encoding='utf-8') as f:
                        content = f.read()
                        result[relative_path][file] = content
                except Exception as e:
                    print(f"Errore nella lettura del file {file_path}: {str(e)}")

    return result

def generate_report(code_contents, output_file='code_report.md'):
    """
    Genera un report in formato Markdown con il contenuto di tutti i file di codice.
    """
    with open(output_file, 'w', encoding='utf-8') as f:
        # Scrivi l'intestazione del report
        f.write(f"# Report del Codice Sorgente\n\n")
        f.write(f"Generato il: {datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n")
        f.write("## Indice dei Contenuti\n\n")
        
        # Genera l'indice
        for folder in code_contents.keys():
            folder_display = '.' if folder == '.' else folder
            f.write(f"- [{folder_display}](#{folder.replace('/', '-')})\n")
            for file in code_contents[folder].keys():
                f.write(f"  - [{file}](#{folder.replace('/', '-')}-{file.replace('.', '-')})\n")
        
        f.write("\n---\n\n")
        
        # Scrivi il contenuto di ogni file
        for folder, files in code_contents.items():
            folder_display = '.' if folder == '.' else folder
            f.write(f"## {folder_display} {'{#' + folder.replace('/', '-') + '}'}\n\n")
            
            for filename, content in files.items():
                f.write(f"### {filename} {'{#' + folder.replace('/', '-') + '-' + filename.replace('.', '-') + '}'}\n\n")
                f.write("```" + os.path.splitext(filename)[1][1:] + "\n")
                f.write(content)
                f.write("\n```\n\n")

def main():
    """
    Funzione principale che esegue la scansione e genera il report.
    """
    try:
        print("Scansione delle directory in corso...")
        code_contents = scan_directory()
        
        if not code_contents:
            print("Nessun file di codice trovato nella directory corrente e nelle sottodirectory.")
            return
        
        output_file = 'code_report.md'
        generate_report(code_contents, output_file)
        print(f"\nReport generato con successo: {os.path.abspath(output_file)}")
        
        # Stampa alcune statistiche
        total_files = sum(len(files) for files in code_contents.values())
        total_dirs = len(code_contents)
        print(f"\nStatistiche:")
        print(f"- Directory scansionate: {total_dirs}")
        print(f"- File di codice trovati: {total_files}")
        
    except Exception as e:
        print(f"Si è verificato un errore: {str(e)}")

if __name__ == "__main__":
    main()