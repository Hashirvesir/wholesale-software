using System;
using System.IO;
using System.IO.Compression;
using System.Reflection;
using System.Diagnostics;
using System.Windows.Forms;
using System.Drawing;

namespace InvencoInstaller
{
    public class Program
    {
        [STAThread]
        public static void Main(string[] args)
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new InstallForm());
        }
    }

    public class InstallForm : Form
    {
        private ProgressBar progressBar;
        private Label statusLabel;

        public InstallForm()
        {
            this.Text = "Invenco Wholesaler - Setup";
            this.Size = new Size(450, 180);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.FromArgb(15, 23, 42);

            Label titleLabel = new Label();
            titleLabel.Text = "Invenco Wholesaler Software";
            titleLabel.ForeColor = Color.White;
            titleLabel.Font = new Font("Segoe UI", 12, FontStyle.Bold);
            titleLabel.Location = new Point(24, 20);
            titleLabel.AutoSize = true;
            this.Controls.Add(titleLabel);

            statusLabel = new Label();
            statusLabel.Text = "Preparing setup...";
            statusLabel.ForeColor = Color.FromArgb(148, 163, 184);
            statusLabel.Font = new Font("Segoe UI", 9);
            statusLabel.Location = new Point(24, 52);
            statusLabel.AutoSize = true;
            this.Controls.Add(statusLabel);

            progressBar = new ProgressBar();
            progressBar.Location = new Point(24, 80);
            progressBar.Size = new Size(385, 24);
            progressBar.Style = ProgressBarStyle.Marquee;
            progressBar.MarqueeAnimationSpeed = 30;
            this.Controls.Add(progressBar);

            this.Shown += async (s, e) => {
                await System.Threading.Tasks.Task.Run(() => PerformInstall());
            };
        }

        private void PerformInstall()
        {
            try
            {
                UpdateStatus("Extracting application files...");

                string localAppData = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData);
                string installDir = Path.Combine(localAppData, "Programs", "Invenco Wholesaler");
                Directory.CreateDirectory(installDir);

                // Get embedded zip stream
                Assembly assembly = Assembly.GetExecutingAssembly();
                using (Stream stream = assembly.GetManifestResourceStream("AppZip"))
                {
                    if (stream != null)
                    {
                        using (ZipArchive archive = new ZipArchive(stream))
                        {
                            foreach (ZipArchiveEntry entry in archive.Entries)
                            {
                                string destPath = Path.Combine(installDir, entry.FullName);
                                if (string.IsNullOrEmpty(entry.Name))
                                {
                                    Directory.CreateDirectory(destPath);
                                }
                                else
                                {
                                    string dir = Path.GetDirectoryName(destPath);
                                    if (!string.IsNullOrEmpty(dir)) Directory.CreateDirectory(dir);
                                    entry.ExtractToFile(destPath, true);
                                }
                            }
                        }
                    }
                    else
                    {
                        // Fallback: look for zip alongside installer
                        string localZip = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "Invenco-Wholesaler-Windows.zip");
                        if (File.Exists(localZip))
                        {
                            ZipFile.ExtractToDirectory(localZip, installDir);
                        }
                    }
                }

                UpdateStatus("Creating desktop shortcut...");
                string targetExe = Path.Combine(installDir, "Invenco Wholesaler.exe");

                CreateShortcut(targetExe);

                UpdateStatus("Launching Invenco Wholesaler...");
                if (File.Exists(targetExe))
                {
                    Process.Start(new ProcessStartInfo
                    {
                        FileName = targetExe,
                        WorkingDirectory = installDir
                    });
                }

                System.Threading.Thread.Sleep(1000);
                this.Invoke((Action)(() => this.Close()));
            }
            catch (Exception ex)
            {
                MessageBox.Show("Installation Error: " + ex.Message, "Setup Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                this.Invoke((Action)(() => this.Close()));
            }
        }

        private void UpdateStatus(string text)
        {
            if (this.InvokeRequired)
            {
                this.Invoke((Action)(() => statusLabel.Text = text));
            }
            else
            {
                statusLabel.Text = text;
            }
        }

        private void CreateShortcut(string targetPath)
        {
            try
            {
                string desktopPath = Environment.GetFolderPath(Environment.SpecialFolder.DesktopDirectory);
                string shortcutPath = Path.Combine(desktopPath, "Invenco Wholesaler.lnk");

                Type t = Type.GetTypeFromCLSID(new Guid("72C24DD5-D70A-438B-8A42-98424B88AFB8")); // WScript.Shell
                dynamic shell = Activator.CreateInstance(t);
                dynamic shortcut = shell.CreateShortcut(shortcutPath);
                shortcut.TargetPath = targetPath;
                shortcut.WorkingDirectory = Path.GetDirectoryName(targetPath);
                shortcut.Description = "Invenco Wholesaler Software";
                shortcut.Save();
            }
            catch {}
        }
    }
}
