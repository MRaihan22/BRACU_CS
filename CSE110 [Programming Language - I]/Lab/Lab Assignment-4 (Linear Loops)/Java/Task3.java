import java.util.Scanner;

public class Task3 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Input n = ");
        int n = sc.nextInt();
        System.out.println("Output: ");

        for (int i = 0; i <= n; i++)
        {
            if (i % 5 == 0 && i % 3 != 0)
            {
                System.out.println(i);
            }
        }
    }
}
